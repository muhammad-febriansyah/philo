<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AboutSettingController extends Controller
{
    private array $keys = [
        'about_title', 'about_tagline', 'about_description',
        'about_vision', 'about_mission',
        'about_founded_year', 'about_total_sessions', 'about_total_branches', 'about_total_clients',
        'about_hero_image_path',
    ];

    public function edit(): View
    {
        $settings = Setting::getMany($this->keys);

        return view('settings.about', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'about_title' => ['required', 'string', 'max:100'],
            'about_tagline' => ['nullable', 'string', 'max:150'],
            'about_description' => ['nullable', 'string', 'max:5000'],
            'about_vision' => ['nullable', 'string', 'max:2000'],
            'about_mission' => ['nullable', 'string', 'max:3000'],
            'about_founded_year' => ['nullable', 'digits:4', 'integer', 'min:2000', 'max:'.date('Y')],
            'about_total_sessions' => ['nullable', 'string', 'max:20'],
            'about_total_branches' => ['nullable', 'string', 'max:20'],
            'about_total_clients' => ['nullable', 'string', 'max:20'],
            'about_hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ]);

        if ($request->hasFile('about_hero_image')) {
            $old = Setting::get('about_hero_image_path');
            if ($old) {
                Storage::disk('public')->delete($old);
            }
            Setting::set('about_hero_image_path', $request->file('about_hero_image')->store('settings/about', 'public'));
        } elseif ($request->boolean('remove_hero_image')) {
            $old = Setting::get('about_hero_image_path');
            if ($old) {
                Storage::disk('public')->delete($old);
            }
            Setting::set('about_hero_image_path', null);
        }

        $textKeys = [
            'about_title', 'about_tagline', 'about_description',
            'about_vision', 'about_mission',
            'about_founded_year', 'about_total_sessions', 'about_total_branches', 'about_total_clients',
        ];

        foreach ($textKeys as $key) {
            Setting::set($key, $request->input($key));
        }

        return redirect()->route('settings.about')->with('success', 'Konten Tentang Kami berhasil disimpan.');
    }
}
