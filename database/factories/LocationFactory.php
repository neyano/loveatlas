<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Location>
 */
class LocationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->city() . ' ' . fake()->streetName(),
            'description' => fake()->optional()->sentence(),
            'latitude' => fake()->latitude(24, 46),     // 日本の緯度範囲
            'longitude' => fake()->longitude(123, 146), // 日本の経度範囲
            'country' => '日本',
            'region' => fake()->prefecture(),
            'city' => fake()->city(),
            'address' => fake()->address(),
            'place_id' => null,
        ];
    }
}
