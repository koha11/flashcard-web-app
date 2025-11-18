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

        $data = $this->service->getAll(
            $request->query('owned-by'),
            $request->query('tags'),
            $request->query('type'),
            $userId,
            $request->query('sort-by', 'date'),
            $request->query('sort-type', 'desc')
        );

        return $data;
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

    public function show($id)
    {
        $collection = $this->service->getById($id);
        return $collection;
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
