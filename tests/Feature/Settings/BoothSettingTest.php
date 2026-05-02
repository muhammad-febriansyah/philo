<?php

use App\Models\Setting;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
});

it('shows the booth setting page', function () {
    $this->actingAs($this->admin)
        ->get(route('settings.booth'))
        ->assertOk()
        ->assertViewIs('settings.booth');
});

it('updates booth pricing settings', function () {
    $this->actingAs($this->admin)
        ->put(route('settings.booth.update'), [
            'booth_base_price' => 30000,
            'booth_extra_print_price' => 7500,
            'booth_max_extra_prints' => 3,
        ])
        ->assertRedirect(route('settings.booth'))
        ->assertSessionHas('success');

    expect(Setting::get('booth_base_price'))->toBe('30000')
        ->and(Setting::get('booth_extra_print_price'))->toBe('7500')
        ->and(Setting::get('booth_max_extra_prints'))->toBe('3');
});

it('parses rupiah-formatted input into integer settings', function () {
    $this->actingAs($this->admin)
        ->put(route('settings.booth.update'), [
            'booth_base_price' => 'Rp 27.500',
            'booth_extra_print_price' => 'Rp 6.000',
            'booth_max_extra_prints' => 4,
        ])
        ->assertRedirect(route('settings.booth'));

    expect(Setting::get('booth_base_price'))->toBe('27500')
        ->and(Setting::get('booth_extra_print_price'))->toBe('6000');
});

it('rejects empty base price', function () {
    $this->actingAs($this->admin)
        ->put(route('settings.booth.update'), [
            'booth_base_price' => '',
            'booth_extra_print_price' => 5000,
            'booth_max_extra_prints' => 2,
        ])
        ->assertSessionHasErrors('booth_base_price');
});

it('rejects non-numeric base price', function () {
    $this->actingAs($this->admin)
        ->put(route('settings.booth.update'), [
            'booth_base_price' => 'abc',
            'booth_extra_print_price' => 5000,
            'booth_max_extra_prints' => 2,
        ])
        ->assertSessionHasErrors('booth_base_price');
});

it('redirects guests to login', function () {
    $this->get(route('settings.booth'))->assertRedirect(route('login'));
});

it('forbids non-admin users', function () {
    $operator = User::factory()->create(['role' => 'operator']);

    $this->actingAs($operator)
        ->get(route('settings.booth'))
        ->assertForbidden();
});
