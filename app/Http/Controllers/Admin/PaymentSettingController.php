<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PaymentSettingController extends Controller
{
    private array $keys = [
        'payment_provider',
        'doku_client_id',
        'doku_secret_key',
        'doku_merchant_id',
        'doku_terminal_id',
        'doku_is_sandbox',
        'duitku_merchant_code',
        'duitku_api_key',
        'duitku_payment_method',
        'duitku_is_sandbox',
        'manual_qris_image_path',
    ];

    public function edit(): View
    {
        $settings = Setting::getMany($this->keys);
        $manualQrisImageUrl = $settings['manual_qris_image_path']
            ? Storage::url($settings['manual_qris_image_path'])
            : null;

        return view('settings.payment', compact('settings', 'manualQrisImageUrl'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payment_provider' => ['required', 'in:doku,duitku,manual'],
            'doku_client_id' => ['nullable', 'string', 'max:100'],
            'doku_secret_key' => ['nullable', 'string', 'max:255'],
            'doku_merchant_id' => ['nullable', 'string', 'max:100'],
            'doku_terminal_id' => ['nullable', 'string', 'max:50'],
            'doku_is_sandbox' => ['nullable', 'boolean'],
            'duitku_merchant_code' => ['nullable', 'string', 'max:100'],
            'duitku_api_key' => ['nullable', 'string', 'max:255'],
            'duitku_payment_method' => ['nullable', 'string', 'max:20'],
            'duitku_is_sandbox' => ['nullable', 'boolean'],
            'manual_qris_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
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

        if ($validated['payment_provider'] === 'manual') {
            $existingPath = Setting::get('manual_qris_image_path');
            if (! $request->hasFile('manual_qris_image') && ! $existingPath) {
                return back()->withErrors(['manual_qris_image' => 'Gambar QRIS wajib diupload untuk metode Manual QRIS.'])->withInput();
            }
        }

        if ($request->hasFile('manual_qris_image')) {
            $oldPath = Setting::get('manual_qris_image_path');
            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('manual_qris_image')->store('qris', 'public');
            Setting::set('manual_qris_image_path', $path);
        }

        Setting::set('payment_provider', $validated['payment_provider']);
        Setting::set('doku_client_id', $request->input('doku_client_id'));
        Setting::set('doku_secret_key', $request->input('doku_secret_key'));
        Setting::set('doku_merchant_id', $request->input('doku_merchant_id'));
        Setting::set('doku_terminal_id', $request->input('doku_terminal_id'));
        Setting::set('doku_is_sandbox', $request->has('doku_is_sandbox') ? '1' : '0');
        Setting::set('duitku_merchant_code', $request->input('duitku_merchant_code'));
        Setting::set('duitku_api_key', $request->input('duitku_api_key'));
        Setting::set('duitku_payment_method', strtoupper((string) $request->input('duitku_payment_method', 'GQ')));
        Setting::set('duitku_is_sandbox', $request->has('duitku_is_sandbox') ? '1' : '0');

        return redirect()->route('settings.payment')->with('success', 'Pengaturan pembayaran berhasil disimpan.');
    }
}
