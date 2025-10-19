<?php

namespace Database\Seeders;

use App\Models\Flashcard;
use Illuminate\Database\Seeder;

class FlashcardSeeder extends Seeder
{
  public function run(): void
  {
    Flashcard::factory()->count(20)->create();
    Flashcard::inRandomOrder()->take(3)->get()->each->delete();
  }
}
