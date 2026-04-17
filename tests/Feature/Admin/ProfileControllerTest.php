<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;

test('guests are redirected to login from profile page', function () {
    get(route('profile.edit'))->assertRedirect(route('login'));
});

test('authenticated users can view profile edit page', function () {
    $user = User::factory()->create();
    actingAs($user);

    get(route('profile.edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('settings/profile'));
});

test('user can update name, email, and phone', function () {
    $user = User::factory()->create(['name' => 'Old Name', 'email' => 'old@example.com']);
    actingAs($user);

    patch(route('profile.update'), [
        'name' => 'New Name',
        'email' => 'new@example.com',
        'phone' => '081234567890',
    ])->assertRedirect(route('profile.edit'))
        ->assertSessionHas('success');

    expect($user->fresh())
        ->name->toBe('New Name')
        ->email->toBe('new@example.com')
        ->phone->toBe('081234567890');
});

test('user can update password', function () {
    $user = User::factory()->create(['password' => Hash::make('OldPass123!')]);
    actingAs($user);

    patch(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'password' => 'NewPass456!',
        'password_confirmation' => 'NewPass456!',
    ])->assertRedirect(route('profile.edit'));

    expect(Hash::check('NewPass456!', $user->fresh()->password))->toBeTrue();
});

test('password is not changed when field is left empty', function () {
    $user = User::factory()->create(['password' => Hash::make('OrigPass789!')]);
    actingAs($user);

    patch(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
    ])->assertRedirect(route('profile.edit'));

    expect(Hash::check('OrigPass789!', $user->fresh()->password))->toBeTrue();
});

test('user can upload avatar', function () {
    Storage::fake('public');
    $user = User::factory()->create(['avatar_path' => null]);
    actingAs($user);

    patch(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'avatar' => UploadedFile::fake()->image('photo.jpg'),
    ])->assertRedirect(route('profile.edit'));

    $freshUser = $user->fresh();
    expect($freshUser->avatar_path)->not->toBeNull();
    Storage::disk('public')->assertExists($freshUser->avatar_path);
});

test('email must be unique excluding current user', function () {
    $existing = User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->create();
    actingAs($user);

    patch(route('profile.update'), [
        'name' => $user->name,
        'email' => 'taken@example.com',
    ])->assertSessionHasErrors('email');
});
