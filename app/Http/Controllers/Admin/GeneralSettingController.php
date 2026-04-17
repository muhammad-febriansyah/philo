<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class GeneralSettingController extends Controller
{
    private array $keys = [
        'site_name', 'site_description', 'logo_path', 'favicon_path',
        'instagram_url', 'facebook_url', 'x_url', 'tiktok_url', 'whatsapp_number', 'footer_text', 'google_maps_embed',
        'booth_countdown_seconds', 'booth_idle_timeout_seconds',
        'print_enabled', 'print_dpi', 'print_default_size',
    ];

    public function edit(): View
    {
        $settings = Setting::getMany($this->keys);

        return view('settings.general', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'site_name'               => ['required', 'string', 'max:100'],
            'site_description'        => ['nullable', 'string', 'max:255'],
            'logo'                    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'favicon'                 => ['nullable', 'image', 'mimes:jpg,jpeg,png,ico,svg', 'max:512'],
            'instagram_url'           => ['nullable', 'url', 'max:255'],
            'facebook_url'            => ['nullable', 'url', 'max:255'],
            'x_url'                   => ['nullable', 'url', 'max:255'],
            'tiktok_url'              => ['nullable', 'url', 'max:255'],
            'whatsapp_number'         => ['nullable', 'string', 'max:20'],
            'footer_text'             => ['nullable', 'string', 'max:255'],
            'google_maps_embed'       => ['nullable', 'string', 'max:5000'],
            'booth_countdown_seconds' => ['required', 'integer', 'min:1', 'max:10'],
            'booth_idle_timeout_seconds' => ['required', 'integer', 'min:10', 'max:300'],
            'print_enabled'           => ['boolean'],
            'print_dpi'               => ['required', 'integer', 'in:72,150,300'],
            'print_default_size'      => ['required', 'string', 'in:strip,A4,A3'],
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $old = Setting::get('logo_path');
            if ($old) {
                Storage::disk('public')->delete($old);
            }
            Setting::set('logo_path', $request->file('logo')->store('settings', 'public'));
        } elseif ($request->boolean('remove_logo')) {
            $old = Setting::get('logo_path');
            if ($old) {
                Storage::disk('public')->delete($old);
            }
            Setting::set('logo_path', null);
        }

        // Handle favicon upload
        if ($request->hasFile('favicon')) {
            $old = Setting::get('favicon_path');
            if ($old) {
                Storage::disk('public')->delete($old);
            }
            Setting::set('favicon_path', $request->file('favicon')->store('settings', 'public'));
        } elseif ($request->boolean('remove_favicon')) {
            $old = Setting::get('favicon_path');
            if ($old) {
                Storage::disk('public')->delete($old);
            }
            Setting::set('favicon_path', null);
        }

        $textKeys = [
            'site_name', 'site_description',
            'instagram_url', 'facebook_url', 'x_url', 'tiktok_url', 'whatsapp_number', 'footer_text', 'google_maps_embed',
            'booth_countdown_seconds', 'booth_idle_timeout_seconds',
            'print_dpi', 'print_default_size',
        ];

        foreach ($textKeys as $key) {
            Setting::set($key, $request->input($key));
        }

        Setting::set('print_enabled', $request->has('print_enabled') ? '1' : '0');

        return redirect()->route('settings.general')->with('success', 'Pengaturan umum berhasil disimpan.');
    }
}
