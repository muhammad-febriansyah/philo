<?php

use App\Models\Branch;
use App\Models\Transaction;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\getJson;

it('can view revenue report page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    actingAs($admin);

    get(route('reports.revenue'))
        ->assertSuccessful()
        ->assertViewIs('reports.revenue')
        ->assertSee('Detail Transaksi');
});

it('can filter revenue report by branch', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $branch = Branch::factory()->create(['name' => 'Jakarta Pusat']);

    Transaction::factory()->paid()->create([
        'branch_id' => $branch->id,
        'amount' => 50000,
        'paid_at' => now(),
    ]);

    actingAs($admin);

    get(route('reports.revenue', ['branch_id' => $branch->id]))
        ->assertSuccessful()
        ->assertSee('Filter cabang aktif')
        ->assertSee('Jakarta Pusat');
});

it('returns revenue datatable json data', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Transaction::factory()->paid()->count(2)->create();

    actingAs($admin);

    getJson(route('reports.revenue.data'))
        ->assertSuccessful()
        ->assertJsonStructure(['data', 'recordsTotal', 'recordsFiltered']);
});

it('can view branch report page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Branch::factory()->create(['name' => 'Bandung']);

    actingAs($admin);

    get(route('reports.branches'))
        ->assertSuccessful()
        ->assertViewIs('reports.branches')
        ->assertSee('Detail Performa Cabang');
});

it('redirects guests from report pages', function () {
    get(route('reports.revenue'))->assertRedirect(route('login'));
    get(route('reports.branches'))->assertRedirect(route('login'));
});
