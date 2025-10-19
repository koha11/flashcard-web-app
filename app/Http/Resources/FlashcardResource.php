<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FlashcardResource extends JsonResource
{
  public function toArray($request): array
  {
    return [
      'id' => $this->id,
      'user_id' => $this->user_id,
      'front_side' => $this->front_side,
      'back_side' => $this->back_side,
      'tags' => $this->tags,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
      'deleted_at' => $this->whenNotNull($this->deleted_at), // useful in admin views
    ];

  }
}
