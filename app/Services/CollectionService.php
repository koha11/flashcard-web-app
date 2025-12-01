<?php

namespace App\Services;

use App\Http\Resources\CollectionResource;
use App\Models\Collection;
use App\Models\Flashcard;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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

    // add is_favorited flag per collection for the given user
    if ($userId) {
      $uid = (int) $userId;
      $collection->addSelect(DB::raw("(exists(select 1 from favorited_collections where favorited_collections.collection_id = collections.id and favorited_collections.user_id = {$uid})) as is_favorited"));
    } else {
      // no user -> always false
      $collection->addSelect(DB::raw('0 as is_favorited'));
    }

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

    $results = $collection->get();

    // normalize to boolean for JSON consumers (cast from 0/1)
    $results->transform(function ($item) {
      $item->is_favorited = (bool) ($item->is_favorited ?? false);
      // provide a stable numeric count field expected by frontend
      $item->flashcard_counted = (int) ($item->flashcards_count ?? 0);
      return $item;
    });

    return $results;
  }

  public function search(array $filters)
  {
    $query = $filters['q'] ?? null;
    $terms = $filters['numberOfTerms'] ?? "all";
    $termMin = 0;
    $termMax = 100;
    if ($terms != "all") {
      if ($terms == "lessThan20") {
        $termMax = 19;
      } else if ($terms == "20To50") {
        $termMin = 20;
        $termMax = 50;
      } else if ($terms == "greaterThan50") {
        $termMin = 50;
        $termMax = 100;
      }
    }
    $sort = $filters['sort'] ?? 'latest';

    $collections = Collection::query()
      ->where('access_level', 'public')
      ->with(['owner'])
      ->withCount([
        'flashcards',
        'favorites',
        'recents',
      ])
      ->when($query, function ($q) use ($query) {
        $q->where(function ($sub) use ($query) {
          $sub->where('name', 'like', "%{$query}%");
        });
      })
      ->when($termMin, function ($q) use ($termMin) {
        $q->having('flashcards_count', '>=', $termMin);
      })
      ->when($termMax, function ($q) use ($termMax) {
        $q->having('flashcards_count', '<=', $termMax);
      })
      ->when($sort, function ($q) use ($sort) {
        switch ($sort) {
          case 'favorited':
            $q->orderBy('favorited_count', 'desc');
            break;
          case 'played':
            $q->orderBy('played_count', 'desc');
            break;
          case 'terms':
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

      ->paginate(2);

    return $collections;
  }



  public function getById($id, $userId)
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

    if ($userId) {
      $this->updateRecentCollections(collection: $collection, userId: $userId);
      $isFavorited = $collection->favorites()->where('user_id', $userId)->exists();
      $collection->is_favorited = $isFavorited;
    } else {
      $collection->is_favorited = false;
      $collection->userId = $userId;
    }

    return $collection;
  }

  public function create(array $data)
  {
    $flashcards = $data['flashcards'] ?? [];
    unset($data['flashcards']);
    unset($data['flashcards']);
    $access_users = $data['access_users'] ?? [];

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
    if (!empty($access_users)) {
      $accessUserIds = [];
      foreach ($access_users as $au) {
        if (isset($au['id'])) {
          $user = User::find($au['id']);
          if ($user) {
            $accessUserIds[] = $user->id;
          }
        }
      }
      $collection->accessUsers()->sync($accessUserIds);
    }
    return $collection->load('flashcards');
  }

  public function update(Collection $collection, array $data)
  {
    $collection->fill($data);
    $collection->save();

    $flashcards = $data['flashcards'] ?? [];
    $access_users = $data['access_users'] ?? [];
    if (!empty($flashcards)) {
      $flashcardIds = [];
      foreach ($flashcards as $fc) {
        if (isset($fc['id'])) {
          $flashcard = Flashcard::find($fc['id']);
          if ($flashcard) {
            $flashcard->update([
              'term' => $fc['term'],
              'definition' => $fc['definition'],
            ]);
            $flashcardIds[] = $flashcard->id;
          }

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
    if (!empty($access_users)) {
      $accessUserIds = [];
      foreach ($access_users as $au) {
        if (isset($au['id'])) {
          $user = User::find($au['id']);
          if ($user) {
            $accessUserIds[] = $user->id;
          }
        }
      }
      $collection->accessUsers()->sync($accessUserIds);
    }
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

  public function updateFavoritedCollections(Collection $collection, $userId, bool $favorite)
  {
    if (!$favorite) {
      $collection->favorites()->detach($userId);
      $collection->favorited_count = max(0, $collection->favorited_count - 1);
      $collection->save();
      return $collection;
    }

    $collection->favorites()->syncWithoutDetaching([
      $userId => ['favorited_date' => now()],
    ]);

    $collection->favorited_count = $collection->favorited_count + 1;
    $collection->save();

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
