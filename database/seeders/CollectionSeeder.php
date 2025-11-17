<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\Flashcard;
use App\Models\User;
use Illuminate\Database\Seeder;

class CollectionSeeder extends Seeder
{
  public function run(): void
  {
    /** 
    * 1. Create owners
    */
    $owners = User::factory()->count(3)->create();

    /** 
    * 2. Create collections
    */
    $collections = $owners->flatMap(function ($owner) {
      return Collection::factory()
        ->count(3)
        ->create(['owner_id' => $owner->id]);
    });

    /**
    * 3. Create flashcards
    */
    $flashcards = Flashcard::factory()->count(30)->create();

    /**
    * 4. Assign flashcards for collection
    */
    $collections->each(function (Collection $collection) use ($flashcards) {
      $collection->flashcards()->syncWithoutDetaching(
        $flashcards->random(rand(5, 12))->pluck('id')->toArray()
      );
    });

    /**
    * 5. Users for access / favorites / recents
    */
    $users = User::factory()->count(5)->create();

    /**
    * 6. Gán quyền truy cập + favorite + recent
    */
    foreach ($collections as $collection) {
      // grant access random users
      $grantUsers = $users->random(rand(1, 3));
      foreach ($grantUsers as $u) {
        $collection->accessUsers()->syncWithoutDetaching([
          $u->id => ['can_edit' => (bool) rand(0, 1)]
        ]);
      }

      // favorites
      $favoriteUsers = $users->random(rand(1, 4));
      foreach ($favoriteUsers as $u) {
        $collection->favorites()->syncWithoutDetaching([
          $u->id => ['favorited_date' => now()->subDays(rand(0, 10))]
        ]);
      }

      // recents
      $recentUsers = $users->random(rand(1, 4));
      foreach ($recentUsers as $u) {
        $collection->recents()->syncWithoutDetaching([
          $u->id => ['viewed_date' => now()->subDays(rand(0, 5))]
        ]);
      }
    }
  }
}
