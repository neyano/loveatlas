<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\User;
use App\Models\Work;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quote>
 */
class QuoteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'work_id' => Work::factory(),
            'location_id' => Location::factory(),
            'quote_text' => fake()->realText(100),
            'character_name' => fake()->optional()->name(),
            'scene_description' => fake()->optional()->sentence(),
            'episode_info' => null,
            'language' => 'ja',
            'photo_path' => null,
            'status' => 'approved',
            'likes_count' => 0,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }
}
