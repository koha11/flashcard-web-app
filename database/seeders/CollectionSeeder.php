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
    // Create some owners
    $owners = User::factory()->count(3)->create();

    // Create collections for each owner
    $collections = $owners->flatMap(
      fn($owner) =>
      Collection::factory()->count(3)->create(['owner_id' => $owner->id])
    );

    // Create flashcards (some for each owner)
    $flashcards = Flashcard::factory()->count(30)->create();

    // Attach flashcards randomly to collections
    $collections->each(function (Collection $c) use ($flashcards) {
      $ids = $flashcards->random(rand(5, 12))->pluck('id');
      $c->flashcards()->syncWithoutDetaching($ids);
    });

    // Access users / favorites / recents (examples)
    $users = User::factory()->count(5)->create();

    foreach ($collections as $c) {
      // grant access to some users
      $usersGrant = $users->random(rand(1, 3));
      foreach ($usersGrant as $u) {
        $c->accessUsers()->syncWithoutDetaching([$u->id => ['can_edit' => (bool) rand(0, 1)]]);
      }

      // favorites
      $usersFav = $users->random(rand(1, 4));
      foreach ($usersFav as $u) {
        $c->favorites()->syncWithoutDetaching([$u->id => ['favorited_date' => now()->subDays(rand(0, 10))]]);
      }

      // recents
      $usersRecent = $users->random(rand(1, 4));
      foreach ($usersRecent as $u) {
        $c->recents()->syncWithoutDetaching([$u->id => ['viewed_date' => now()->subDays(rand(0, 5))]]);
      }
    }
  }
}
