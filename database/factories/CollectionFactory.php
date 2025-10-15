<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Collection>
 */
class CollectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'name' => $this->faker->title(),
            'tags' => $this->faker->word(),
            'access_level' => $this->faker->word(),
            'played_count' => $this->faker->randomNumber(4),
            'favorited_count' => $this->faker->randomNumber(4),
        ];
    }
}
