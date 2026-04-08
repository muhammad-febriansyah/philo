<?php

use App\Models\Branch;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
});

it('can view branch list', function () {
    $this->actingAs($this->admin)
        ->get(route('branches.index'))
        ->assertOk()
        ->assertViewIs('branches.index');
});

it('returns datatable json data', function () {
    Branch::factory(3)->create();

    $this->actingAs($this->admin)
        ->getJson(route('branches.data'))
        ->assertOk()
        ->assertJsonStructure(['data', 'recordsTotal', 'recordsFiltered']);
});

it('can view create branch form', function () {
    $this->actingAs($this->admin)
        ->get(route('branches.create'))
        ->assertOk()
        ->assertViewIs('branches.create');
});

it('can create a branch', function () {
    $this->actingAs($this->admin)
        ->post(route('branches.store'), [
            'name' => 'Philo Jakarta',
            'code' => 'JKT-01',
            'phone' => '08123456789',
            'address' => 'Jl. Sudirman No.1',
            'is_active' => true,
        ])
        ->assertRedirect(route('branches.index'))
        ->assertSessionHas('success');

    expect(Branch::where('code', 'JKT-01')->exists())->toBeTrue();
});

it('validates required fields on store', function () {
    $this->actingAs($this->admin)
        ->post(route('branches.store'), [])
        ->assertSessionHasErrors(['name', 'code']);
});

it('validates unique code on store', function () {
    Branch::factory()->create(['code' => 'JKT-01']);

    $this->actingAs($this->admin)
        ->post(route('branches.store'), [
            'name' => 'Philo Jakarta 2',
            'code' => 'JKT-01',
        ])
        ->assertSessionHasErrors('code');
});

it('can view edit branch form', function () {
    $branch = Branch::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('branches.edit', $branch))
        ->assertOk()
        ->assertViewIs('branches.edit')
        ->assertViewHas('branch', $branch);
});

it('can update a branch', function () {
    $branch = Branch::factory()->create(['name' => 'Old Name']);

    $this->actingAs($this->admin)
        ->put(route('branches.update', $branch), [
            'name' => 'New Name',
            'code' => $branch->code,
            'is_active' => true,
        ])
        ->assertRedirect(route('branches.index'))
        ->assertSessionHas('success');

    expect($branch->fresh()->name)->toBe('New Name');
});

it('can delete a branch', function () {
    $branch = Branch::factory()->create();

    $this->actingAs($this->admin)
        ->deleteJson(route('branches.destroy', $branch))
        ->assertOk()
        ->assertJsonPath('message', 'Cabang berhasil dihapus.');

    expect(Branch::find($branch->id))->toBeNull();
});

it('redirects guests to login', function () {
    $this->get(route('branches.index'))->assertRedirect(route('login'));
});
