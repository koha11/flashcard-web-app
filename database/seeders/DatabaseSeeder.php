<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  public function run(): void
  {
    $this->call([
      FlashcardSeeder::class,
      // (and your Collection seeder if you want links)
    ]);
  }
}
