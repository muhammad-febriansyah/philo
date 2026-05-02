<?php

use App\Models\Branch;
use App\Models\Package;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\Voucher;

beforeEach(function () {
    $this->branch = Branch::factory()->create();
    $this->package = Package::factory()->create(['is_active' => true]);

    Setting::set('booth_base_price', '25000');
    Setting::set('booth_extra_print_price', '5000');
    Setting::set('booth_max_extra_prints', '5');
    Setting::set('payment_provider', 'manual');
});

it('creates a transaction at the booth base price with no extras', function () {
    $response = $this->postJson(route('booth.session.start'), [
        'branch_id' => $this->branch->id,
        'extra_prints' => 0,
    ])->assertOk();

    $tx = Transaction::firstOrFail();

    expect($tx->amount)->toBe(25000)
        ->and((int) $tx->extra_prints)->toBe(0)
        ->and($tx->voucher_id)->toBeNull();

    $response->assertJsonPath('amount', 25000);
});

it('adds extra-print fees to transaction amount', function () {
    $this->postJson(route('booth.session.start'), [
        'branch_id' => $this->branch->id,
        'extra_prints' => 3,
    ])->assertOk();

    $tx = Transaction::firstOrFail();

    // 25.000 + 3 × 5.000 = 40.000
    expect($tx->amount)->toBe(40000)
        ->and((int) $tx->extra_prints)->toBe(3);
});

it('clamps extra_prints to the configured max', function () {
    $this->postJson(route('booth.session.start'), [
        'branch_id' => $this->branch->id,
        'extra_prints' => 99,
    ])->assertOk();

    $tx = Transaction::firstOrFail();

    // capped at 5 → 25.000 + 25.000 = 50.000
    expect((int) $tx->extra_prints)->toBe(5)
        ->and($tx->amount)->toBe(50000);
});

it('applies a usable voucher inline at session start', function () {
    Voucher::factory()->create([
        'code' => 'PROMO20',
        'type' => Voucher::TYPE_PERCENTAGE,
        'value' => 20,
    ]);

    $this->postJson(route('booth.session.start'), [
        'branch_id' => $this->branch->id,
        'extra_prints' => 0,
        'voucher_code' => 'PROMO20',
    ])->assertOk();

    $tx = Transaction::firstOrFail();

    expect($tx->amount)->toBe(20000)
        ->and((int) $tx->discount_amount)->toBe(5000)
        ->and((int) $tx->original_amount)->toBe(25000);
});

it('reissue cancels the previous transaction and creates a new one with voucher applied', function () {
    Voucher::factory()->create([
        'code' => 'REISSUE10',
        'type' => Voucher::TYPE_PERCENTAGE,
        'value' => 10,
    ]);

    // First, create a transaction without voucher
    $this->postJson(route('booth.session.start'), [
        'branch_id' => $this->branch->id,
        'extra_prints' => 2,
    ])->assertOk();

    $original = Transaction::firstOrFail();
    expect($original->amount)->toBe(35000); // 25k + 2×5k

    // Now reissue with voucher
    $this->postJson(route('booth.session.reissue'), [
        'transaction_id' => $original->id,
        'voucher_code' => 'REISSUE10',
    ])->assertOk();

    $original->refresh();
    expect($original->status)->toBe('cancelled');

    $reissued = Transaction::where('id', '!=', $original->id)->firstOrFail();
    expect((int) $reissued->extra_prints)->toBe(2)
        ->and($reissued->amount)->toBe(31500) // 35k − 10%
        ->and((int) $reissued->discount_amount)->toBe(3500);
});

it('reissue refuses if transaction is already paid', function () {
    $this->postJson(route('booth.session.start'), [
        'branch_id' => $this->branch->id,
        'extra_prints' => 0,
    ])->assertOk();

    $tx = Transaction::firstOrFail();
    $tx->markAsPaid();

    $this->postJson(route('booth.session.reissue'), [
        'transaction_id' => $tx->id,
        'voucher_code' => null,
    ])->assertStatus(422);
});
