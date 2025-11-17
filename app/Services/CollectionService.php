<?php

namespace App\Services;

use App\Http\Resources\CollectionResource;
use App\Models\Collection;

class CollectionService
{
  public function getAll($ownedBy, $tags, $type, $userId, $sortBy = 'date', $sortType = 'desc')
  {
    $query = Collection::query()
      ->with('flashcards') // eager-load flashcards if you want them in resource
      // filter by explicit owner (owned-by param)
      ->when($ownedBy, function ($q) use ($ownedBy) {
        $q->where('owner_id', $ownedBy);
      })
      // filter by tags (simple LIKE search on tags column)
      ->when($tags !== '', function ($q) use ($tags) {
        $tagList = array_filter(array_map('trim', explode(',', $tags)));

        if (!empty($tagList)) {
          $q->where(function ($inner) use ($tagList) {
            foreach ($tagList as $tag) {
              $inner->orWhere('tags', 'like', "%{$tag}%");
            }
          });
        }
      });

    // TYPE FILTERS
    $query
      // owned by current user (if owned-by is not used)
      ->when($type === 'owned' && !$ownedBy && $userId, function ($q) use ($userId) {
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

    // SORTING
    switch ($sortBy) {
      case 'views':
        // map "views" to played_count
        $query->orderBy('played_count', $sortType);
        break;

      case 'favorite':
        // map "favorite" to favorited_count
        $query->orderBy('favorited_count', $sortType);
        break;

      case 'date':
      default:
        // default: sort by created_at
        $query->orderBy('created_at', $sortType);
        break;
    }

    return $query->paginate();
  }

  public function create(array $data)
  {
    return Collection::create($data);
  }

  public function update(Collection $collection, array $data)
  {
    $collection->update($data);
    return $collection->fresh();
  }

  public function delete(Collection $collection)
  {
    return $collection->delete();
  }
}
