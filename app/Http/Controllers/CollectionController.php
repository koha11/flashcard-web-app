<?php

namespace App\Http\Controllers;

use App\Http\Resources\CollectionResource;
use App\Models\Collection;
use App\Services\CollectionService;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CollectionController extends Controller
{
    protected CollectionService $service;

    protected GeminiService $geminiService;

    public function __construct(CollectionService $service, GeminiService $geminiService)
    {
        $this->service = $service;
        $this->geminiService = $geminiService;
    }

    public function index(Request $request)
    {
        $userId = $request->user()?->id;

        $query = Collection::query()
            ->with('flashcards')
            // Filter by owner explicitly (owned-by=123)
            ->when(
                $request->filled('owned-by'),
                fn($q) => $q->where('owner_id', $request->integer('owned-by'))
            )
            // Filter by tags (tags=tag1,tag2,...)
            ->when(
                $request->filled('tags'),
                function ($q) use ($request) {
                    $tags = explode(',', $request->string('tags')->toString());

                    $q->where(function ($inner) use ($tags) {
                        foreach ($tags as $tag) {
                            $tag = trim($tag);
                            if ($tag === '') {
                                continue;
                            }
                            // adjust if you store tags differently
                            $inner->orWhere('tags', 'like', "%{$tag}%");
                        }
                    });
                }
            );

        // Handle "type" filter
        $type = $request->string('type')->toString();

        $query
            // collections owned by current user
            ->when($type === 'owned' && $userId, fn($q) => $q->where('owner_id', $userId))
            // public collections
            ->when($type === 'public', fn($q) => $q->where('access_level', 'public'))
            // shared with me (simplified: access_level = 'shared')
            ->when($type === 'shared with me', fn($q) => $q->where('access_level', 'shared'));
        // "recently" and "favorited" we’ll treat mainly as sort, not filter

        // Sorting
        $sortBy = $request->string('sort-by')->toString() ?: 'date';
        $sortType = strtolower($request->string('sort-type')->toString() ?: 'desc');

        if (!in_array($sortType, ['asc', 'desc'], true)) {
            $sortType = 'desc';
        }

        switch ($sortBy) {
            case 'views':
                // assumes you have a 'views_count' column
                $query->orderBy('views_count', $sortType);
                break;

            case 'favorite':
                // assumes you have a 'favorites_count' column
                $query->orderBy('favorites_count', $sortType);
                break;

            case 'date':
            default:
                // for "recently" we usually want newest first
                $query->orderBy('created_at', $sortType);
                break;
        }

        // If type = recently and no explicit sort-by was provided,
        // you can force date DESC:
        if ($type === 'recently' && !$request->filled('sort-by')) {
            $query->reorder()->orderBy('created_at', 'desc');
        }

        return CollectionResource::collection($query->paginate());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'tags' => ['sometimes', 'nullable', 'string'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'access_level' => ['sometimes', Rule::in(['private', 'public', 'shared'])],
        ]);

        $data['owner_id'] = $data['owner_id'] ?? $request->user()->id ?? null;

        return $this->service->create($data);
    }

    public function show(Collection $collection)
    {
        return new CollectionResource($collection);
    }

    public function update(Request $request, Collection $collection)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'tags' => ['sometimes', 'nullable', 'string'],
            'access_level' => ['sometimes', 'required', Rule::in(['private', 'public', 'shared'])],
            'owner_id' => ['prohibited'], // avoid changing ownership via API
        ]);

        $collection->update($data);

        return new CollectionResource($collection->fresh());
    }

    public function destroy(Collection $collection)
    {
        $collection->delete(); // hard delete (no softDeletes on this table)
        return response()->noContent();
    }

    public function extract(Request $request)
    {
        $payload = $request->validate(['content' => ['required', 'string']]);

        $config = [
            "system" => [
                "parts" => [
                    "text" => "Bạn có nhiệm vụ giúp người dùng trích xuất các từ mới từ đoạn văn bản được cung cấp (đoạn văn bản đó có thể bằng bất kỳ ngôn ngữ nào), trả về dạng JSON với 2 key term là từ gốc trong văn bản, definition là nghĩa của từ đó bằng tiếng việt."
                ]
            ],
            "config" => [
                "responseMimeType" => "application/json",
                "responseSchema" => [
                    "type" => "ARRAY",
                    "items" => [
                        "type" => "OBJECT",
                        "properties" => [
                            "term" => ["type" => "STRING"],
                            "definition" => ["type" => "STRING"]
                        ],
                        "propertyOrdering" => ["term", "definition"]
                    ]
                ]
            ]
        ];

        $answer = $this->geminiService->prompt($payload['content'], $config);

        $data = json_decode($answer, true);

        return json_encode(compact('data'));
    }
}
