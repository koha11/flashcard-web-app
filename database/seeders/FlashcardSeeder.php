<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Flashcard;
use App\Models\User;

class FlashcardSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::factory()->count(5)->create();

        Flashcard::factory()
            ->count(60)
            ->state(fn() => ['user_id' => $users->random()->id])
            ->create();
    }
}
