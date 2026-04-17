<?php

namespace Database\Factories;

use App\Models\Gallery;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Gallery>
 */
class GalleryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'image_path' => 'gallery/'.fake()->lexify('????????').'.jpg',
            'sort_order' => fake()->numberBetween(0, 10),
            'is_active' => true,
        ];
    }
}
