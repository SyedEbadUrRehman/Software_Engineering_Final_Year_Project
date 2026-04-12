<?php

namespace Database\Factories;

use Carbon\Carbon;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
             'name' => $this->faker->name(),

            // fake profile file/image
            'file' => "https://loremflickr.com/320/240?random=".mt_rand(1, 50),

            'email' => $this->faker->unique()->safeEmail(),

            'email_verified_at' => Carbon::now(),

            'password' => Hash::make('password'),

            'remember_token' => Str::random(10),

            // Carbon timestamps
            'created_at' => Carbon::now()->subDays(rand(1, 30)),
            'updated_at' => Carbon::now(),

           ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
