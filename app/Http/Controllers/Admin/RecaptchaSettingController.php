<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecaptchaSettingController extends Controller
{
    private array $keys = [
        'recaptcha_enabled',
        'recaptcha_site_key',
        'recaptcha_secret_key',
    ];

    public function edit(): View
    {
        $settings = Setting::getMany($this->keys);

        return view('settings.recaptcha', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'recaptcha_enabled' => ['nullable', 'boolean'],
            'recaptcha_site_key' => ['nullable', 'string', 'max:255'],
            'recaptcha_secret_key' => ['nullable', 'string', 'max:255'],
        ]);

        if ($request->boolean('recaptcha_enabled')) {
            $request->validate([
                'recaptcha_site_key' => ['required', 'string', 'max:255'],
                'recaptcha_secret_key' => ['required', 'string', 'max:255'],
            ]);
        }

        Setting::set('recaptcha_enabled', $request->has('recaptcha_enabled') ? '1' : '0');
        Setting::set('recaptcha_site_key', $validated['recaptcha_site_key'] ?? null);
        Setting::set('recaptcha_secret_key', $validated['recaptcha_secret_key'] ?? null);

        return redirect()->route('settings.recaptcha')->with('success', 'Pengaturan reCAPTCHA berhasil disimpan.');
    }
}
