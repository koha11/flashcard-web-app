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
            'term' => ucfirst($this->faker->words(2, true)),
            'definition' => $this->faker->sentence(10),
        ];
    }
}
