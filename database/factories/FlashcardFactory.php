<?php

namespace Database\Factories;

use App\Models\Flashcard;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FlashcardFactory extends Factory
{
    protected $model = Flashcard::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'front_side' => ucfirst($this->faker->words(2, true)),
            'back_side' => $this->faker->sentence(10),
            'tags' => 'English,Dev,Remote job',
        ];
    }
}
