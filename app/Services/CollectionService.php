<?php

namespace App\Services;

use App\Http\Resources\CollectionResource;
use App\Models\Collection;
use App\Models\Flashcard;

class CollectionService
{
  public function getAll($ownedBy, $type, $userId)
  {
    $collection = Collection::query()->with([
      'owner',
      'flashcards',
    ])->withCount([
          'flashcards',
        ]);

    $collection
      // no type -> all collections that the user has access to
      ->when($userId && $type === null, function ($q) use ($userId) {
        $q->where('owner_id', $userId);
      })
      // public collections
      ->when($type === 'public', function ($q) {
        $q->where('access_level', 'public');
      })
      // shared with me (via collection_access_users pivot)
      ->when($type === 'shared with me' && $userId, function ($q) use ($userId) {
        $q->whereHas('accessUsers', function ($sub) use ($userId) {
          $sub->whereKey($userId); // users.id = $userId
        });
      })
      // favorited by current user (via favorited_collections pivot)
      ->when($type === 'favorited' && $userId, function ($q) use ($userId) {
        $q->whereHas('favorites', function ($sub) use ($userId) {
          $sub->whereKey($userId);
        });
      })
      // recently viewed by current user (via recent_collections pivot)
      ->when($type === 'recently' && $userId, function ($q) use ($userId) {
        $q->whereHas('recents', function ($sub) use ($userId) {
          $sub->whereKey($userId);
        });
      });

    return $collection->get();
    // $query = Collection::query()
    //   ->withCount('flashcards') // eager-load flashcards if you want them in resource
    //   // filter by explicit owner (owned-by param)
    //   ->when($ownedBy, function ($q) use ($ownedBy) {
    //     $q->where('owner_id', $ownedBy);
    //   })
    //   // filter by tags (simple LIKE search on tags column)
    //   ->when($tags !== '', function ($q) use ($tags) {
    //     $tagList = array_filter(array_map('trim', explode(',', $tags)));

    //     if (!empty($tagList)) {
    //       $q->where(function ($inner) use ($tagList) {
    //         foreach ($tagList as $tag) {
    //           $inner->orWhere('tags', 'like', "%{$tag}%");
    //         }
    //       });
    //     }
    //   });

    // // TYPE FILTERS
    // $query
    //   // owned by current user (if owned-by is not used)
    //   ->when($type === 'owned' && !$ownedBy && $userId, function ($q) use ($userId) {
    //     $q->where('owner_id', $userId);
    //   })
    //   // public collections
    //   ->when($type === 'public', function ($q) {
    //     $q->where('access_level', 'public');
    //   })
    //   // shared with me (via collection_access_users pivot)
    //   ->when($type === 'shared with me' && $userId, function ($q) use ($userId) {
    //     $q->whereHas('accessUsers', function ($sub) use ($userId) {
    //       $sub->whereKey($userId); // users.id = $userId
    //     });
    //   })
    //   // favorited by current user (via favorited_collections pivot)
    //   ->when($type === 'favorited' && $userId, function ($q) use ($userId) {
    //     $q->whereHas('favorites', function ($sub) use ($userId) {
    //       $sub->whereKey($userId);
    //     });
    //   })
    //   // recently viewed by current user (via recent_collections pivot)
    //   ->when($type === 'recently' && $userId, function ($q) use ($userId) {
    //     $q->whereHas('recents', function ($sub) use ($userId) {
    //       $sub->whereKey($userId);
    //     });
    //   });

    // // SORTING
    // switch ($sortBy) {
    //   case 'views':
    //     // map "views" to played_count
    //     $query->orderBy('played_count', $sortType);
    //     break;

    //   case 'favorite':
    //     // map "favorite" to favorited_count
    //     $query->orderBy('favorited_count', $sortType);
    //     break;

    //   case 'date':
    //   default:
    //     // default: sort by created_at
    //     $query->orderBy('created_at', $sortType);
    //     break;
    // }

    // return $query->get();
  }
  
  public function search(array $filters)
  {
    $query = $filters['q'] ?? null;
    $termMin = $filters['term_min'] ?? null;
    $termMax = $filters['term_max'] ?? null;
    $sort = $filters['sort'] ?? 'latest';  

    $collections = Collection::query()
        ->where('access_level', 'public')
        ->when($query, function ($q) use ($query) {
            $q->where(function ($sub) use ($query) {
                $sub->where('name', 'like', "%{$query}%");
            });
        })
        ->when($termMin, function ($q) use ($termMin) {
            $q->where('flashcards_count', '>=', $termMin);
        })
        ->when($termMax, function ($q) use ($termMax) {
            $q->where('flashcards_count', '<=', $termMax);
        })
        ->when($sort, function ($q) use ($sort) {
            switch ($sort) {
                case 'favorited':
                    $q->orderBy('favorited_count', 'desc');
                    break;
                case 'played':
                    $q->orderBy('played_count', 'desc');
                    break;
                case 'flashcards':
                    $q->orderBy('flashcards_count', 'desc');
                    break;
                case 'oldest':
                    $q->orderBy('created_at', 'asc');
                    break;
                case 'latest':
                default:
                    $q->orderBy('created_at', 'desc');
                    break;
            }
        })

        ->paginate(12);

    return $collections;
  }



  public function getById($id)
  {
    $collection = Collection::with([
      'owner',
      'flashcards',
      'accessUsers'
    ])
      ->withCount([
        'flashcards',
        'favorites',
        'recents',
      ])
      ->findOrFail($id);

    // if ($collection and $collection->get('viewed_count') !== $userId) {
    //   $this->updateRecentCollections($collection, $userId);
    // }

    return $collection;
  }

  public function create(array $data)
  {
    $flashcards = $data['flashcards'] ?? [];
    unset($data['flashcards']);

    $collection = Collection::create($data);

    if (!empty($flashcards)) {
      $flashcardIds = [];

      foreach ($flashcards as $fc) {
        $flashcard = Flashcard::create([
          'term' => $fc['term'],
          'definition' => $fc['definition'],
        ]);
        $flashcardIds[] = $flashcard->id;
      }
      $collection->flashcards()->attach($flashcardIds);
    }
    return $collection->load('flashcards');
  }

  public function update(Collection $collection, array $data)
  {
    $collection->fill($data);
    $collection->save();

    $flashcards = $data['flashcards'] ?? [];

    if (!empty($flashcards)) {
      $flashcardIds = [];
      foreach ($flashcards as $fc) {
        if (isset($fc['id'])) {
          $flashcard = Flashcard::find($fc['id']);
          $flashcard->update([
            'term' => $fc['term'],
            'definition' => $fc['definition'],
          ]);
          $flashcardIds[] = $flashcard->id;

        } else {
          $flashcard = Flashcard::create([
            'term' => $fc['term'],
            'definition' => $fc['definition'],
          ]);
          $flashcardIds[] = $flashcard->id;
        }
      }
    }

    $collection->flashcards()->sync($flashcardIds);
    return $collection->load('flashcards');
  }

  public function updateRecentCollections(Collection $collection, $userId)
  {
    $collection->updateOrFail(["viewed_count" => $collection->viewed_count + 1]);

    $collection->recents()->syncWithoutDetaching([
      $userId => ['viewed_date' => now()],
    ]);

    return $collection;
  }

  public function addFlashcard(Collection $collection, $flashcardId)
  {
    $collection->flashcards()->attach($flashcardId);
    return $collection->fresh();
  }

  public function removeFlashcard(Collection $collection, $flashcardId)
  {
    $collection->flashcards()->detach($flashcardId);
    return $collection->fresh();
  }

  public function delete(Collection $collection)
  {
    $collection->flashcards()->delete();
    return $collection->delete();
  }
}
