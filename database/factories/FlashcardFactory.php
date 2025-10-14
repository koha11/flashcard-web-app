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
        $tags = collect($this->faker->randomElements(
            ['English', 'Dev', 'Remote job', 'Database', 'Design', 'Grammar', 'Docker', 'API', 'React', 'Laravel'],
            rand(0, 3)
        ))->implode(',');

        return [
            'user_id' => User::factory(),
            'front_side' => ucfirst($this->faker->unique()->words(rand(1, 3), true)),
            'back_side' => $this->faker->sentence(rand(8, 14)),
            'tags' => $tags,
        ];
    }

}
