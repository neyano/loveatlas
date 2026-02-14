<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Work>
 */
class WorkFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'title_original' => fake()->optional()->sentence(3),
            'type' => fake()->randomElement(['movie', 'anime', 'drama', 'novel', 'game', 'other']),
            'year' => fake()->optional()->numberBetween(1980, 2026),
            'country' => fake()->optional()->country(),
            'description' => fake()->optional()->paragraph(),
            'poster_path' => null,
            'external_url' => null,
            'submitted_by' => User::factory(),
            'is_approved' => true,
        ];
    }
}
