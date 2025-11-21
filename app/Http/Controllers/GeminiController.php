<?php

use App\Services\GeminiService;

class GeminiController {

  protected GeminiService $service;

  public function __construct(GeminiService $service)
  {
    $this->service = $service;
  }

  public function autoGenBaseOnTagsAndDescription() {

  }

}


?>