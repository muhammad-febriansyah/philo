<?php

namespace App\Http\Requests;

use App\Models\Template;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdatePackageRequest extends FormRequest
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
            'photo_count' => ['required', 'integer', 'min:1'],
            'print_size' => ['required', 'string', 'max:100'],
            'price' => ['required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'template_ids' => ['sometimes', 'array'],
            'template_ids.*' => ['integer', 'exists:templates,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama paket',
            'description' => 'deskripsi',
            'photo_count' => 'jumlah foto',
            'print_size' => 'ukuran cetak',
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

            $photoCount = (int) $this->input('photo_count');
            $printSize = $this->normalizePrintSize((string) $this->input('print_size'));

            $templates = Template::query()
                ->whereIn('id', $templateIds)
                ->get(['id', 'name', 'photo_slots', 'print_size']);

            foreach ($templates as $template) {
                if ((int) $template->photo_slots !== $photoCount) {
                    $validator->errors()->add(
                        'template_ids',
                        "Template {$template->name} punya {$template->photo_slots} slot, jadi tidak cocok untuk paket {$photoCount} foto.",
                    );
                }

                if ($this->normalizePrintSize((string) $template->print_size) !== $printSize) {
                    $validator->errors()->add(
                        'template_ids',
                        "Template {$template->name} berukuran {$template->print_size}, jadi tidak cocok untuk paket {$this->input('print_size')}.",
                    );
                }
            }
        });
    }

    private function normalizePrintSize(string $value): string
    {
        return strtoupper(trim($value));
    }
}
