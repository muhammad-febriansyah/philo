<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'max:255'],
            'print_size'     => ['required', 'string', 'max:50'],
            'photo_slots'    => ['required', 'integer', 'min:1', 'max:20'],
            'frame'          => ['required', 'file', 'mimes:png,webp', 'max:5120'],
            'thumbnail'      => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'slot_positions' => ['nullable', 'json'],
            'is_active'      => ['sometimes', 'boolean'],
        ];
    }
}
