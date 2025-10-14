<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFlashcardRequest;
use App\Http\Requests\UpdateFlashcardRequest;
use App\Http\Resources\FlashcardResource;
use App\Models\Flashcard;
use Illuminate\Http\Request;


class FlashcardController extends Controller
{
    public function index()
    {
        $cards = Flashcard::query()
            ->with('user:id,name')
            ->notDeleted()
            ->latest('id')
            ->paginate(15);

        return response()->json($cards);
    }

    public function __invoke(Request $request)
    {
        $cards = Flashcard::query()
            ->with('user:id,name')
            ->latest('id')
            ->paginate(15);

        return response()->json($cards);
    }

    // public function store(StoreFlashcardRequest $request)
    // {
    //     $card = Flashcard::create($request->validated() + [
    //         'user_id' => $request->user()->id,
    //     ]);

    //     return (new FlashcardResource($card))->response()->setStatusCode(201);
    // }

    public function show(Flashcard $flashcard)
    {
        return new FlashcardResource($flashcard->load('user:id,name'));
    }

    // public function update(UpdateFlashcardRequest $request, Flashcard $flashcard)
    // {
    //     // Optional: authorization (policy) → $this->authorize('update', $flashcard);
    //     $flashcard->update($request->validated());
    //     return new FlashcardResource($flashcard);
    // }

    public function destroy(Flashcard $flashcard)
    {
        // soft “deleted” field as per your schema
        $flashcard->update(['deleted' => true]);
        return response()->noContent();
    }
}
