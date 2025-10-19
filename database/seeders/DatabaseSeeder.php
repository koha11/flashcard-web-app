<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  public function run(): void
  {
    // If you need a baseline admin/user first, seed here.
    // \App\Models\User::factory()->create([...]);

    $this->call([
      CollectionSeeder::class,
      FlashcardSeeder::class, // optional if CollectionSeeder already creates many
    ]);
  }

}
