<?php

use App\Models\Branch;
use App\Models\Package;
use App\Models\PhotoSession;
use App\Models\Template;
use App\Models\Transaction;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('guests are redirected to the login page', function () {
    $response = get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    actingAs($user);

    $response = get(route('dashboard'));
    $response->assertOk()
        ->assertViewIs('dashboard')
        ->assertSee('Revenue 7 Hari Terakhir')
        ->assertSee('Aktivitas Cabang Hari Ini')
        ->assertSee('Sesi Hari Ini');
});

test('dashboard shows recent operational data', function () {
    $user = User::factory()->create();
    $branch = Branch::factory()->create(['name' => 'Jakarta Barat Branch', 'code' => 'JKT-BRT']);
    $package = Package::factory()->create(['name' => 'Strip Premium']);
    $template = Template::factory()->create(['name' => 'Neon Frame']);

    $transaction = Transaction::factory()->paid()->create([
        'branch_id' => $branch->id,
        'package_id' => $package->id,
        'amount' => 50000,
        'paid_at' => now(),
    ]);

    PhotoSession::factory()->completed()->create([
        'transaction_id' => $transaction->id,
        'branch_id' => $branch->id,
        'template_id' => $template->id,
    ]);

    actingAs($user);

    get(route('dashboard'))
        ->assertOk()
        ->assertSee($transaction->order_id)
        ->assertSee('Jakarta Barat Branch')
        ->assertSee('Strip Premium')
        ->assertSee('Neon Frame');
});
