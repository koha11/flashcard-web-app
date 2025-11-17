<?php

namespace App\Services;

use App\Models\Collection;

class CollectionService
{
  public function getAll()
  {
    return Collection::latest()->get();
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
