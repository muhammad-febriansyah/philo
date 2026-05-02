<?php

use App\Models\Branch;
use App\Models\Package;
use App\Models\User;
use App\Models\Voucher;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
});

it('can view voucher index', function () {
    $this->actingAs($this->admin)
        ->get(route('vouchers.index'))
        ->assertOk()
        ->assertViewIs('vouchers.index');
});

it('returns datatable json data', function () {
    Voucher::factory(3)->create();

    $this->actingAs($this->admin)
        ->getJson(route('vouchers.data'))
        ->assertOk()
        ->assertJsonStructure(['data', 'recordsTotal', 'recordsFiltered']);
});

it('can view create voucher form', function () {
    $this->actingAs($this->admin)
        ->get(route('vouchers.create'))
        ->assertOk()
        ->assertViewIs('vouchers.create');
});

it('can create a percentage voucher', function () {
    $this->actingAs($this->admin)
        ->post(route('vouchers.store'), [
            'code' => 'PROMO20',
            'name' => 'Promo 20%',
            'type' => 'percentage',
            'value' => 20,
            'max_uses' => 100,
            'is_active' => true,
        ])
        ->assertRedirect(route('vouchers.index'))
        ->assertSessionHas('success');

    expect(Voucher::where('code', 'PROMO20')->exists())->toBeTrue();
});

it('upcases voucher code on create', function () {
    $this->actingAs($this->admin)
        ->post(route('vouchers.store'), [
            'code' => 'promo10',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
        ])
        ->assertRedirect(route('vouchers.index'));

    expect(Voucher::where('code', 'PROMO10')->exists())->toBeTrue();
});

it('caps percentage value at 100', function () {
    $this->actingAs($this->admin)
        ->post(route('vouchers.store'), [
            'code' => 'OVERFLOW',
            'type' => 'percentage',
            'value' => 250,
            'is_active' => true,
        ])
        ->assertRedirect(route('vouchers.index'));

    expect((float) Voucher::where('code', 'OVERFLOW')->value('value'))->toBe(100.0);
});

it('rejects duplicate code', function () {
    Voucher::factory()->create(['code' => 'DUPLICATE']);

    $this->actingAs($this->admin)
        ->post(route('vouchers.store'), [
            'code' => 'DUPLICATE',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
        ])
        ->assertSessionHasErrors('code');
});

it('rejects invalid character in code', function () {
    $this->actingAs($this->admin)
        ->post(route('vouchers.store'), [
            'code' => 'BAD CODE!',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
        ])
        ->assertSessionHasErrors('code');
});

it('can update a voucher', function () {
    $voucher = Voucher::factory()->create(['name' => 'Old']);

    $this->actingAs($this->admin)
        ->put(route('vouchers.update', $voucher), [
            'code' => $voucher->code,
            'name' => 'New',
            'type' => 'percentage',
            'value' => 15,
            'is_active' => true,
        ])
        ->assertRedirect(route('vouchers.index'));

    expect($voucher->fresh()->name)->toBe('New')
        ->and((float) $voucher->fresh()->value)->toBe(15.0);
});

it('can delete a voucher', function () {
    $voucher = Voucher::factory()->create();

    $this->actingAs($this->admin)
        ->deleteJson(route('vouchers.destroy', $voucher))
        ->assertOk()
        ->assertJsonPath('message', 'Voucher berhasil dihapus.');

    expect(Voucher::find($voucher->id))->toBeNull();
});

it('can bulk-generate vouchers', function () {
    $this->actingAs($this->admin)
        ->post(route('vouchers.bulk.store'), [
            'quantity' => 5,
            'prefix' => 'BULK-',
            'type' => 'percentage',
            'value' => 10,
            'max_uses' => 1,
        ])
        ->assertRedirect(route('vouchers.index'));

    expect(Voucher::where('source', Voucher::SOURCE_BULK)->count())->toBe(5)
        ->and(Voucher::where('code', 'like', 'BULK-%')->count())->toBe(5);
});

it('blocks bulk quantity above 500', function () {
    $this->actingAs($this->admin)
        ->post(route('vouchers.bulk.store'), [
            'quantity' => 600,
            'type' => 'percentage',
            'value' => 10,
        ])
        ->assertSessionHasErrors('quantity');
});

it('redirects guests away from voucher index', function () {
    $this->get(route('vouchers.index'))->assertRedirect(route('login'));
});

it('forbids non-admin users', function () {
    $operator = User::factory()->create(['role' => 'operator']);

    $this->actingAs($operator)
        ->get(route('vouchers.index'))
        ->assertForbidden();
});

it('persists package and branch scopes', function () {
    $package = Package::factory()->create();
    $branch = Branch::factory()->create();

    $this->actingAs($this->admin)
        ->post(route('vouchers.store'), [
            'code' => 'SCOPED',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
            'applicable_packages' => [$package->id],
            'applicable_branches' => [$branch->id],
        ])
        ->assertRedirect(route('vouchers.index'));

    $voucher = Voucher::where('code', 'SCOPED')->first();
    expect($voucher->applicable_packages)->toBe([$package->id])
        ->and($voucher->applicable_branches)->toBe([$branch->id]);
});
