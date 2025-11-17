<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CollectionResource extends JsonResource
{
  public function toArray($request): array
  {
    return [
      'id' => $this->id,
      'name' => $this->name,
      'tags' => $this->tags,
      'owner_id' => $this->owner_id,
      'access_level' => $this->access_level,
      'played_count' => $this->played_count,
      'favorited_count' => $this->favorited_count,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
      // Optional: small summary of relations
      'flashcards' => FlashcardResource::collection(
        $this->whenLoaded('flashcards')
      ),
    ];
  }
}
