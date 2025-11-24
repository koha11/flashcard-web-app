<?php

namespace Database\Factories;

use App\Enums\CollectionAccessLevel;
use App\Models\Collection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CollectionFactory extends Factory
{
    protected $model = Collection::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentences(mt_rand(1, 3), true),
            'tags' => $this->faker->optional()->words(3, true),
            'owner_id' => User::factory(),
            'access_level' => $this->faker->randomElement(CollectionAccessLevel::cases())->value,
            'viewed_count' => $this->faker->numberBetween(0, 500),
            'favorited_count' => $this->faker->numberBetween(0, 500),
        ];
    }
}
