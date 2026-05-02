<?php

namespace App\Http\Requests;

use App\Models\Voucher;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1', 'max:500'],
            'prefix' => ['nullable', 'string', 'max:16', 'regex:/^[A-Z0-9_-]*$/'],
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
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'prefix' => strtoupper((string) $this->input('prefix')),
        ]);

        if ($this->input('type') === Voucher::TYPE_PERCENTAGE) {
            $this->merge([
                'value' => min(100, (float) $this->input('value', 0)),
            ]);
        }
    }
}
