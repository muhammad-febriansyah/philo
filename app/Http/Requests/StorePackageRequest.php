<?php

namespace App\Http\Requests;

use App\Models\Template;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'print_copies' => ['required', 'integer', 'min:1', 'max:10'],
            'price' => ['required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'template_ids' => ['required', 'array', 'min:1'],
            'template_ids.*' => ['integer', 'exists:templates,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama paket',
            'description' => 'deskripsi',
            'print_copies' => 'jumlah cetak',
            'price' => 'harga',
            'template_ids' => 'template',
            'template_ids.*' => 'template',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'integer' => ':attribute harus berupa angka.',
            'min' => ':attribute minimal :min.',
            'max' => ':attribute maksimal :max karakter.',
            'array' => ':attribute tidak valid.',
            'template_ids.required' => 'Pilih minimal 1 template untuk paket ini.',
            'template_ids.min' => 'Pilih minimal 1 template untuk paket ini.',
            'template_ids.*.exists' => 'Template yang dipilih tidak ditemukan.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $templateIds = $this->input('template_ids', []);

            if (! is_array($templateIds) || $templateIds === []) {
                return;
            }

            $templates = Template::query()
                ->whereIn('id', $templateIds)
                ->get(['id', 'name', 'photo_slots', 'print_size']);

            $sizes = $templates->pluck('print_size')->map(fn ($s) => strtoupper(trim((string) $s)))->unique();
            $slots = $templates->pluck('photo_slots')->map(fn ($n) => (int) $n)->unique();

            if ($sizes->count() > 1) {
                $validator->errors()->add(
                    'template_ids',
                    'Semua template dalam satu paket harus punya ukuran cetak yang sama. Ditemukan: '.$sizes->implode(', ').'.',
                );
            }

            if ($slots->count() > 1) {
                $validator->errors()->add(
                    'template_ids',
                    'Semua template dalam satu paket harus punya jumlah slot foto yang sama. Ditemukan: '.$slots->implode(', ').' slot.',
                );
            }
        });
    }
}
