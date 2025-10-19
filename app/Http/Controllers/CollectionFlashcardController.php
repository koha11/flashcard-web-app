<?php

namespace App\Http\Controllers;

use App\Http\Resources\FlashcardResource;
use App\Models\Collection;
use App\Models\Flashcard;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CollectionFlashcardController extends Controller
{
  /**
   * GET /api/v1/collections/{collection}/flashcards
   * List flashcards in a collection (paginated).
   * Query params (optional):
   *   q=... (search in front_side/back_side/tags)
   *   include_trashed=1 (include soft-deleted flashcards)
   */
  public function index(Request $request, Collection $collection)
  {
    $query = $collection->flashcards()->getQuery();

    // include soft-deleted if requested
    if ($request->boolean('include_trashed')) {
      $query->withTrashed();
    }

    if ($request->filled('q')) {
      $term = '%' . $request->string('q')->toString() . '%';
      $query->where(function ($q) use ($term) {
        $q->where('front_side', 'like', $term)
          ->orWhere('back_side', 'like', $term)
          ->orWhere('tags', 'like', $term);
      });
    }

    return FlashcardResource::collection(
      $query->orderByDesc('collection_flashcard.created_at')->paginate()
    );
  }

  /**
   * POST /api/v1/collections/{collection}/flashcards/{flashcard}
   * Attach one flashcard to a collection.
   */
  public function attach(Collection $collection, Flashcard $flashcard)
  {
    // If you have policies, you can authorize here, e.g.:
    // $this->authorize('update', $collection);

    $collection->flashcards()->syncWithoutDetaching([$flashcard->getKey()]);
    return response()->noContent();
  }

  /**
   * DELETE /api/v1/collections/{collection}/flashcards/{flashcard}
   * Detach a flashcard from a collection.
   */
  public function detach(Collection $collection, Flashcard $flashcard)
  {
    // $this->authorize('update', $collection);

    $collection->flashcards()->detach($flashcard->getKey());
    return response()->noContent();
  }

  /**
   * POST /api/v1/collections/{collection}/flashcards/sync
   * Body JSON:
   *   { "flashcard_ids": [1,2,3] }
   * Replaces the full set of attached flashcards with the provided list.
   */
  public function sync(Request $request, Collection $collection)
  {
    // $this->authorize('update', $collection);

    $data = $request->validate([
      'flashcard_ids' => ['required', 'array', 'min:0'],
      'flashcard_ids.*' => [
        'integer',
        'distinct',
        Rule::exists('flashcards', 'id')->where(function ($q) {
          // exclude soft-deleted flashcards from being attached (optional)
          $q->whereNull('deleted_at');
        }),
      ],
    ]);

    $collection->flashcards()->sync($data['flashcard_ids']);
    return response()->noContent();
  }
}
