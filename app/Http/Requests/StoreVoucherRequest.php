<?php

namespace App\Http\Requests;

use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:64', 'regex:/^[A-Z0-9_-]+$/', Rule::unique('vouchers', 'code')],
            'name' => ['nullable', 'string', 'max:255'],
            'type' => ['required', Rule::in([Voucher::TYPE_PERCENTAGE, Voucher::TYPE_FIXED])],
            'value' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'min_purchase' => ['nullable', 'numeric', 'min:0'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'applicable_packages' => ['nullable', 'array'],
            'applicable_packages.*' => ['integer', Rule::exists('packages', 'id')],
            'applicable_branches' => ['nullable', 'array'],
            'applicable_branches.*' => ['integer', Rule::exists('branches', 'id')],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function prepareForValidation(): void
    {
        $type = $this->input('type');

        $this->merge([
            'code' => strtoupper((string) $this->input('code')),
            'is_active' => $this->boolean('is_active'),
            'value' => $type === Voucher::TYPE_FIXED
                ? $this->normalizeCurrency($this->input('value'))
                : $this->normalizeDecimal($this->input('value')),
            'min_purchase' => $this->normalizeCurrency($this->input('min_purchase')),
            'valid_from' => $this->normalizeDate($this->input('valid_from'), false),
            'valid_until' => $this->normalizeDate($this->input('valid_until'), true),
        ]);

        if ($type === Voucher::TYPE_PERCENTAGE) {
            $this->merge([
                'value' => min(100, (float) $this->input('value', 0)),
            ]);
        }
    }

    private function normalizeCurrency(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', (string) $value);

        return $digits === '' ? null : (int) $digits;
    }

    private function normalizeDecimal(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        if ($normalized === '') {
            return null;
        }

        $normalized = preg_replace('/[^0-9,.\-]/', '', $normalized) ?? '';

        if (str_contains($normalized, ',')) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        }

        return $normalized === '' ? null : (float) $normalized;
    }

    private function normalizeDate(mixed $value, bool $endOfDay): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        try {
            $date = Carbon::createFromFormat('d/m/Y', $value);
        } catch (\Throwable) {
            try {
                $date = Carbon::parse($value);
            } catch (\Throwable) {
                return $value;
            }
        }

        return ($endOfDay ? $date->endOfDay() : $date->startOfDay())->toDateTimeString();
    }

    public function messages(): array
    {
        return [
            'code.regex' => 'Kode hanya boleh berisi huruf besar, angka, garis bawah, dan strip.',
            'code.unique' => 'Kode voucher sudah digunakan.',
            'valid_until.after_or_equal' => 'Tanggal berakhir harus setelah tanggal mulai.',
        ];
    }
}
