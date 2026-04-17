<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentSettingController extends Controller
{
    private array $keys = [
        'payment_provider',
        'doku_client_id',
        'doku_secret_key',
        'doku_is_sandbox',
        'duitku_merchant_code',
        'duitku_api_key',
        'duitku_payment_method',
        'duitku_is_sandbox',
    ];

    public function edit(): View
    {
        $settings = Setting::getMany($this->keys);

        return view('settings.payment', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payment_provider' => ['required', 'in:doku,duitku'],
            'doku_client_id' => ['nullable', 'string', 'max:100'],
            'doku_secret_key' => ['nullable', 'string', 'max:255'],
            'doku_is_sandbox' => ['nullable', 'boolean'],
            'duitku_merchant_code' => ['nullable', 'string', 'max:100'],
            'duitku_api_key' => ['nullable', 'string', 'max:255'],
            'duitku_payment_method' => ['nullable', 'string', 'max:20'],
            'duitku_is_sandbox' => ['nullable', 'boolean'],
        ]);

        if ($validated['payment_provider'] === 'doku') {
            $request->validate([
                'doku_client_id' => ['required', 'string', 'max:100'],
                'doku_secret_key' => ['required', 'string', 'max:255'],
            ]);
        }

        if ($validated['payment_provider'] === 'duitku') {
            $request->validate([
                'duitku_merchant_code' => ['required', 'string', 'max:100'],
                'duitku_api_key' => ['required', 'string', 'max:255'],
                'duitku_payment_method' => ['required', 'string', 'max:20'],
            ]);
        }

        Setting::set('payment_provider', $validated['payment_provider']);
        Setting::set('doku_client_id', $request->input('doku_client_id'));
        Setting::set('doku_secret_key', $request->input('doku_secret_key'));
        Setting::set('doku_is_sandbox', $request->has('doku_is_sandbox') ? '1' : '0');
        Setting::set('duitku_merchant_code', $request->input('duitku_merchant_code'));
        Setting::set('duitku_api_key', $request->input('duitku_api_key'));
        Setting::set('duitku_payment_method', strtoupper((string) $request->input('duitku_payment_method', 'GQ')));
        Setting::set('duitku_is_sandbox', $request->has('duitku_is_sandbox') ? '1' : '0');

        return redirect()->route('settings.payment')->with('success', 'Pengaturan pembayaran berhasil disimpan.');
    }
}
