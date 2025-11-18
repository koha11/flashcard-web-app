<?php

namespace App\Services;

use App\Models\Flashcard;

class FlashcardService
{

  public function create(array $data)
  {
    return Flashcard::create($data);
  }

  public function update(Flashcard $flashcard, array $data)
  {
    $flashcard->update($data);
    return $flashcard->fresh();
  }

  public function delete(Flashcard $flashcard)
  {
    return $flashcard->delete();
  }
}
