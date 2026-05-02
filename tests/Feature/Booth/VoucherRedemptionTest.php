<?php

use App\Models\Branch;
use App\Models\Package;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\Voucher;

beforeEach(function () {
    $this->branch = Branch::factory()->create();
    // The booth always uses the first active package internally.
    $this->package = Package::factory()->create(['is_active' => true]);

    Setting::set('booth_base_price', '25000');
    Setting::set('booth_extra_print_price', '5000');
    Setting::set('booth_max_extra_prints', '5');
});

it('validates a usable percentage voucher and returns discount', function () {
    $voucher = Voucher::factory()->create([
        'code' => 'PROMO20',
        'type' => Voucher::TYPE_PERCENTAGE,
        'value' => 20,
    ]);

    $this->postJson(route('booth.voucher.validate'), [
        'code' => 'PROMO20',
        'branch_id' => $this->branch->id,
        'extra_prints' => 0,
    ])
        ->assertOk()
        ->assertJsonPath('valid', true)
        ->assertJsonPath('voucher.id', $voucher->id)
        ->assertJsonPath('discount_amount', 5000)
        ->assertJsonPath('final_amount', 20000);
});

it('applies discount on the booth total including extra prints', function () {
    Voucher::factory()->create([
        'code' => 'PROMO10',
        'type' => Voucher::TYPE_PERCENTAGE,
        'value' => 10,
    ]);

    // base 25.000 + 2 × 5.000 = 35.000 → 10% discount = 3.500
    $this->postJson(route('booth.voucher.validate'), [
        'code' => 'PROMO10',
        'branch_id' => $this->branch->id,
        'extra_prints' => 2,
    ])
        ->assertOk()
        ->assertJsonPath('valid', true)
        ->assertJsonPath('original_amount', 35000)
        ->assertJsonPath('discount_amount', 3500)
        ->assertJsonPath('final_amount', 31500);
});

it('rejects an unknown voucher code', function () {
    $this->postJson(route('booth.voucher.validate'), [
        'code' => 'NOPE',
        'branch_id' => $this->branch->id,
    ])
        ->assertOk()
        ->assertJsonPath('valid', false);
});

it('rejects an expired voucher', function () {
    Voucher::factory()->expired()->create(['code' => 'OLD']);

    $this->postJson(route('booth.voucher.validate'), [
        'code' => 'OLD',
        'branch_id' => $this->branch->id,
    ])
        ->assertOk()
        ->assertJsonPath('valid', false);
});

it('rejects voucher restricted to other packages', function () {
    $otherPackage = Package::factory()->create();
    Voucher::factory()->create([
        'code' => 'SCOPED',
        'applicable_packages' => [$otherPackage->id],
    ]);

    $this->postJson(route('booth.voucher.validate'), [
        'code' => 'SCOPED',
        'branch_id' => $this->branch->id,
    ])
        ->assertOk()
        ->assertJsonPath('valid', false);
});

it('rejects voucher when below minimum purchase', function () {
    Voucher::factory()->create([
        'code' => 'BIGSPEND',
        'min_purchase' => 100000,
    ]);

    $this->postJson(route('booth.voucher.validate'), [
        'code' => 'BIGSPEND',
        'branch_id' => $this->branch->id,
    ])
        ->assertOk()
        ->assertJsonPath('valid', false);
});

it('rejects exhausted voucher', function () {
    Voucher::factory()->create([
        'code' => 'GONE',
        'max_uses' => 1,
        'uses_count' => 1,
    ]);

    $this->postJson(route('booth.voucher.validate'), [
        'code' => 'GONE',
        'branch_id' => $this->branch->id,
    ])
        ->assertOk()
        ->assertJsonPath('valid', false);
});

it('increments uses_count when transaction transitions to paid', function () {
    $voucher = Voucher::factory()->create(['uses_count' => 0]);
    $transaction = Transaction::factory()->create([
        'voucher_id' => $voucher->id,
        'discount_amount' => 5000,
        'original_amount' => 25000,
    ]);

    $marked = $transaction->markAsPaid();

    expect($marked)->toBeTrue();
    expect((int) $voucher->fresh()->uses_count)->toBe(1);
});

it('only increments uses_count once for repeated markAsPaid calls', function () {
    $voucher = Voucher::factory()->create(['uses_count' => 0]);
    $transaction = Transaction::factory()->create(['voucher_id' => $voucher->id]);

    expect($transaction->markAsPaid())->toBeTrue();
    expect($transaction->markAsPaid())->toBeFalse();
    expect((int) $voucher->fresh()->uses_count)->toBe(1);
});

it('does not increment when transaction has no voucher', function () {
    $transaction = Transaction::factory()->create(['voucher_id' => null]);

    expect($transaction->markAsPaid())->toBeTrue();
    expect($transaction->fresh()->status)->toBe('paid');
});
