<?php

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'dob' => fake()->date(),
        ];
    }

    /**
     * After creating the user, automatically create Account.
     */
    public function configure()
    {
        return $this->afterCreating(function ($user) {

            Account::create([
                'id' => $user->id,            
                'email' => $user->email,     
                'password' => Hash::make('123123'), 
                'email_verified_at' => now(),
            ]);

        });
    }

    
    
}
