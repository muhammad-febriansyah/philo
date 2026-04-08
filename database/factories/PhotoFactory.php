<?php

namespace Database\Factories;

use App\Models\Photo;
use App\Models\PhotoSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Photo>
 */
class PhotoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'photo_session_id' => PhotoSession::factory(),
            'original_path' => 'photos/' . fake()->uuid() . '.jpg',
            'order' => fake()->numberBetween(1, 6),
        ];
    }
}
