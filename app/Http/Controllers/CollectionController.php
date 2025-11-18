<?php

namespace App\Http\Controllers;

use App\Http\Resources\CollectionResource;
use App\Models\Collection;
use App\Services\CollectionService;
use App\Services\FlashcardService;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CollectionController extends Controller
{
    protected CollectionService $service;

    protected GeminiService $geminiService;

    protected FlashcardService $flashcardService;


    public function __construct(CollectionService $service, GeminiService $geminiService, FlashcardService $flashcardService)
    {
        $this->service = $service;
        $this->geminiService = $geminiService;
        $this->flashcardService = $flashcardService;
    }

    public function index(Request $request)
    {
        // $userId = $request->user()?->id;
        $userId = 1;

        $data = $this->service->getAll(
            $request->query('owned-by'),
            $request->query('type'),
            $userId,
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

    public function storeFlashcards(Request $request, Collection $collection)
    {
        $data = $request->validate([
            'flashcards' => ['required', 'array', 'min:1'],
            'flashcards.*.term' => ['required', 'string', 'max:255'],
            'flashcards.*.definition' => ['required', 'string'],
        ]);

        $flashcardIds = [];
        $newFlashcards = [];

        foreach ($data['flashcards'] as $flashcardData) {
            $flashcard = $this->flashcardService->create($flashcardData);
            $newFlashcards[] = $flashcard;
            $flashcardIds[] = $flashcard->id;
        }

        $this->service->addFlashcard($collection, $flashcardIds);

        // Return the newly added flashcards
        return response()->json(['flashcards' => $newFlashcards]);
    }

    public function updateFlashcard(Request $request, Collection $collection)
    {
        $data = $request->validate([
            'flashcard_id' => ['required', 'exists:flashcards,id'],
            'term' => ['sometimes', 'required', 'string', 'max:255'],
            'definition' => ['sometimes', 'required', 'string'],
        ]);

        $flashcard = $collection->flashcards()->where('flashcards.id', $data['flashcard_id'])->firstOrFail();

        $flashcard->update($data);

        return response()->json(['flashcard' => $flashcard->fresh()]);
    }

    public function destroyFlashcard(Request $request, Collection $collection)
    {
        $data = $request->validate([
            'flashcard_id' => ['required', 'exists:flashcards,id'],
        ]);

        $flashcard = $collection->flashcards()->where('flashcards.id', $data['flashcard_id'])->firstOrFail();

        $collection->flashcards()->detach($flashcard->id);

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
