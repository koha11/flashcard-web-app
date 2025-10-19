<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFlashcardRequest;
use App\Http\Requests\UpdateFlashcardRequest;
use App\Http\Resources\FlashcardResource;
use App\Models\Flashcard;
use Illuminate\Http\Request;

class FlashcardController extends Controller
{
    public function index(Request $request)
    {
        $query = Flashcard::query()
            ->when($request->filled('user_id'), fn($q) => $q->where('user_id', $request->integer('user_id')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%' . $request->string('q')->toString() . '%';
                $q->where(function ($x) use ($term) {
                    $x->where('front_side', 'like', $term)
                        ->orWhere('back_side', 'like', $term)
                        ->orWhere('tags', 'like', $term);
                });
            });

        return FlashcardResource::collection($query->latest()->paginate());
    }

    public function store(Request $request)
    {
        $data = $request->validated();
        $data['user_id'] = $data['user_id'] ?? $request->user()->id ?? null;

        $card = Flashcard::create($data);

        // optional attach to collections on create
        if ($request->filled('collection_ids')) {
            $card->collections()->sync($request->input('collection_ids', []));
        }

        return new FlashcardResource($card);
    }

    public function show(Flashcard $flashcard)
    {
        return new FlashcardResource($flashcard);
    }

    public function update(Request $request, Flashcard $flashcard)
    {
        $flashcard->update($request->validated());

        if ($request->filled('collection_ids')) {
            $flashcard->collections()->sync($request->input('collection_ids', []));
        }

        return new FlashcardResource($flashcard->fresh());
    }

    public function destroy(Flashcard $flashcard)
    {
        $flashcard->delete(); // soft delete
        return response()->noContent();
    }

    // Soft-delete helpers (optional but handy)
    public function trashed()
    {
        return FlashcardResource::collection(
            Flashcard::onlyTrashed()->latest('deleted_at')->paginate()
        );
    }

    public function restore($id)
    {
        $card = Flashcard::withTrashed()->findOrFail($id);
        $card->restore();
        return new FlashcardResource($card);
    }

    public function forceDelete($id)
    {
        $card = Flashcard::withTrashed()->findOrFail($id);
        $card->forceDelete();
        return response()->noContent();
    }

}
