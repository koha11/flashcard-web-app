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
        $userId = $request->user()->user->id;
      

        $data = $this->service->getAll(
            $request->query('owned-by'),
            $request->query('type'),
            $userId,
        );

        return $data;
    }
    public function search(Request $request)
    {
        return response()->json($this->service->search($request->all()));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:250'],
            'tags' => ['sometimes', 'nullable', 'string'],
            'description' => ['sometimes', 'nullable', 'string'],
            'access_level' => ['sometimes', Rule::in(['private', 'public', 'shared'])],
            'access_users' => ['sometimes', 'nullable', 'array'],
            'flashcards' => ['required', 'array'],
            'flashcards.*.term' => ['required', 'string', 'max:255'],
            'flashcards.*.definition' => ['required', 'string', 'max:255'],
        ]);
        
        $data['owner_id'] = $request->user()->user->id;

        return $this->service->create($data);
    }

    public function show(Request $request, $id)
    {
        $account = $request->user();
        $userId = $account ? $account->user->id : null;
        $collection = $this->service->getById($id, $userId);
        return $collection;
    }

    public function update(Request $request, Collection $collection)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:250'],
            'tags' => ['sometimes', 'nullable', 'string'],
            'access_level' => [ 'required', Rule::in(['private', 'public', 'shared'])],
            'access_users' => ['sometimes', 'nullable', 'array'],
            'owner_id' => ['prohibited'], // avoid changing ownership via API
            'flashcards' => ['required', 'array'],
            'flashcards.*.term' => ['required', 'string', 'max:255'],
            'flashcards.*.definition' => ['required', 'string', 'max:255'],
        ]);

        $result = $this->service->update($collection, $data);

        return $result;
    }

    public function destroy(Collection $collection)
    {
        $collection->delete(); // hard delete (no softDeletes on this table)
        return response()->noContent();
    }

    public function storeFlashcard(Request $request, Collection $collection)
    {
        $data = $request->validate([
            'term' => ['required', 'string', 'min:1', 'max:255'],
            'definition' => ['required', 'string', 'min:1', 'max:255'],
        ]);

        $flashcard = $this->flashcardService->create($data);
        
        return $this->service->addFlashcard($collection, $flashcard->id);
    }

    public function updateFlashcard(Request $request, Collection $collection)
    {
        $data = $request->validate([
            'id' => ['required', 'exists:flashcards,id'],
            'term' => ['required', 'string', 'max:255'],
            'definition' => ['required', 'string'],
        ]);

        $flashcard = $this->flashcardService->update(
            $data['id'],
            [
                'term' => $data['term'],
                'definition' => $data['definition'],
            ]
        );

        return response()->json(['flashcard' => $flashcard->fresh()]);
    }

    public function destroyFlashcard(Collection $collection, $flashcard_id)
    {

        $this->service->removeFlashcard($collection, $flashcard_id);

        $this->flashcardService->delete($flashcard_id);

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

        $flashcards = json_decode($answer, true);

        return response()->json(compact('flashcards'));
    }

    public function autoGenBaseOnDescription(Request $request)
    {
        $payload = $request->validate(
            [
                'description' => ['required', 'string'],
            ]
        );

        $response = $this->geminiService->autoGenBaseOnDescription($payload['description']);

        $data = json_decode($response, true);

        return json_encode(compact('data'), JSON_UNESCAPED_UNICODE);

    }
}
