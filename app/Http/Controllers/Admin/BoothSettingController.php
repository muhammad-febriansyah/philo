<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BoothSettingController extends Controller
{
    /** @var array<int, string> */
    private array $keys = [
        'booth_base_price',
        'booth_extra_print_price',
        'booth_max_extra_prints',
    ];

    public function edit(): View
    {
        $settings = Setting::getMany($this->keys);

        return view('settings.booth', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->merge([
            'booth_base_price' => $this->normalizeCurrency($request->input('booth_base_price')),
            'booth_extra_print_price' => $this->normalizeCurrency($request->input('booth_extra_print_price')),
        ]);

        $validated = $request->validate([
            'booth_base_price' => ['required', 'integer', 'min:0', 'max:100000000'],
            'booth_extra_print_price' => ['required', 'integer', 'min:0', 'max:100000000'],
            'booth_max_extra_prints' => ['required', 'integer', 'min:0', 'max:99'],
        ], [
            'booth_base_price.required' => 'Harga sesi wajib diisi.',
            'booth_extra_print_price.required' => 'Harga cetak tambahan wajib diisi.',
            'booth_max_extra_prints.required' => 'Maksimal cetak tambahan wajib diisi.',
        ]);

        Setting::set('booth_base_price', (string) $validated['booth_base_price']);
        Setting::set('booth_extra_print_price', (string) $validated['booth_extra_print_price']);
        Setting::set('booth_max_extra_prints', (string) $validated['booth_max_extra_prints']);

        return redirect()->route('settings.booth')->with('success', 'Pengaturan harga booth berhasil disimpan.');
    }

    private function normalizeCurrency(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', (string) $value);

        return $digits === '' ? null : (int) $digits;
    }
}
