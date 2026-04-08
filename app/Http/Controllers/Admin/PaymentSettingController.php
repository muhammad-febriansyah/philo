<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentSettingController extends Controller
{
    private array $keys = ['duitku_merchant_code', 'duitku_api_key', 'duitku_is_sandbox'];

    public function edit(): View
    {
        $settings = Setting::getMany($this->keys);

        return view('settings.payment', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'duitku_merchant_code' => ['required', 'string', 'max:100'],
            'duitku_api_key'       => ['required', 'string', 'max:255'],
            'duitku_is_sandbox'    => ['nullable', 'boolean'],
        ]);

        Setting::set('duitku_merchant_code', $request->input('duitku_merchant_code'));
        Setting::set('duitku_api_key', $request->input('duitku_api_key'));
        Setting::set('duitku_is_sandbox', $request->has('duitku_is_sandbox') ? '1' : '0');

        return redirect()->route('settings.payment')->with('success', 'Pengaturan pembayaran berhasil disimpan.');
    }
}
