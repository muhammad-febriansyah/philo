<?php

namespace Database\Factories;

use App\Models\Template;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Template>
 */
class TemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true) . ' Frame',
            'thumbnail_path' => null,
            'frame_path' => 'templates/default.png',
            'print_size' => fake()->randomElement(['strip', 'A4', 'A3']),
            'photo_slots' => fake()->randomElement([2, 4, 6]),
            'slot_positions' => [
                ['x' => 50, 'y' => 50, 'width' => 400, 'height' => 300],
                ['x' => 50, 'y' => 400, 'width' => 400, 'height' => 300],
            ],
            'is_active' => true,
        ];
    }
}
