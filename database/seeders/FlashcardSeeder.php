<?php

namespace Database\Seeders;

use App\Models\Flashcard;
use Illuminate\Database\Seeder;

class FlashcardSeeder extends Seeder
{
  public function run(): void
  {
    Flashcard::factory()->count(40)->create();
  }
}
