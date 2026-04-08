<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\PhotoSession;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PhotoSession>
 */
class PhotoSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'transaction_id' => Transaction::factory(),
            'branch_id' => Branch::factory(),
            'status' => 'capturing',
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => 'completed',
            'completed_at' => now(),
            'final_image_path' => 'sessions/final-' . fake()->uuid() . '.png',
        ]);
    }
}
