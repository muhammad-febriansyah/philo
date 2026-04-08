<?php

namespace Database\Factories;

use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Package>
 */
class PackageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Strip 2x6', 'Grid A4', 'Grid A3', 'Single A4']),
            'description' => fake()->sentence(),
            'photo_count' => fake()->randomElement([2, 4, 6]),
            'print_size' => fake()->randomElement(['strip', 'A4', 'A3']),
            'price' => fake()->randomElement([20000, 35000, 50000]),
            'is_active' => true,
        ];
    }
}
