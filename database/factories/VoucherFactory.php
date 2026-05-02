<?php

namespace Database\Factories;

use App\Models\Voucher;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Voucher>
 */
class VoucherFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'TEST-'.strtoupper(Str::random(6)),
            'name' => fake()->words(2, true),
            'type' => Voucher::TYPE_PERCENTAGE,
            'value' => 10,
            'max_uses' => null,
            'uses_count' => 0,
            'min_purchase' => null,
            'valid_from' => null,
            'valid_until' => null,
            'applicable_packages' => null,
            'applicable_branches' => null,
            'is_active' => true,
            'source' => Voucher::SOURCE_MANUAL,
            'created_by' => null,
        ];
    }

    public function fixed(int $amount = 5000): static
    {
        return $this->state(fn () => [
            'type' => Voucher::TYPE_FIXED,
            'value' => $amount,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'valid_until' => now()->subDay(),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }
}
