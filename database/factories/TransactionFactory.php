<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Package;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => 'PB-' . fake()->unique()->numerify('######'),
            'branch_id' => Branch::factory(),
            'package_id' => Package::factory(),
            'amount' => fake()->randomElement([20000, 35000, 50000]),
            'payment_method' => 'qris',
            'status' => 'pending',
            'expired_at' => now()->addMinutes(15),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => 'paid',
            'paid_at' => now(),
            'duitku_reference' => fake()->uuid(),
        ]);
    }
}
