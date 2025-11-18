<?php

namespace App\Services;

use App\Models\Flashcard;

class FlashcardService
{

  public function create(array $data)
  {
    return Flashcard::create($data);
  }

  public function update($flashcard_id, array $data)
  {
    $flashcard = Flashcard::findOrFail($flashcard_id);
    $flashcard->update($data);
    return $flashcard->fresh();
  }

  public function delete($flashcard_id)
  {
    $flashcard = Flashcard::findOrFail($flashcard_id);
    return $flashcard->delete();
  }
}
