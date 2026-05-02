<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'print_size' => ['required', 'string', 'max:50'],
            'photo_slots' => ['required', 'integer', 'min:1', 'max:20'],
            'thumbnail' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:30720', 'dimensions:min_width=800,min_height=800'],
            'slot_positions' => ['nullable', 'json'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'thumbnail.required' => 'Gambar frame wajib diunggah.',
            'thumbnail.file' => 'File gambar frame tidak valid.',
            'thumbnail.uploaded' => 'Gambar frame gagal diunggah. Pastikan ukuran file tidak melebihi 30 MB.',
            'thumbnail.mimes' => 'Gambar frame harus berformat JPG, PNG, atau WebP.',
            'thumbnail.max' => 'Ukuran gambar frame tidak boleh melebihi 30 MB.',
            'thumbnail.dimensions' => 'Resolusi gambar frame minimal 800×800 px (disarankan ≥ 1200 px untuk cetak berkualitas).',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $photoSlots = (int) $this->input('photo_slots', 0);
            $rawSlots = $this->input('slot_positions');

            if (! is_string($rawSlots) || trim($rawSlots) === '') {
                $validator->errors()->add('slot_positions', 'Posisi slot foto wajib diisi.');

                return;
            }

            $decoded = json_decode($rawSlots, true);

            if (! is_array($decoded)) {
                $validator->errors()->add('slot_positions', 'Format posisi slot foto tidak valid.');

                return;
            }

            if (count($decoded) !== $photoSlots) {
                $validator->errors()->add(
                    'slot_positions',
                    "Jumlah slot yang digambar harus sama dengan jumlah slot foto ({$photoSlots}).",
                );
            }

            foreach ($decoded as $index => $slot) {
                if (! is_array($slot)) {
                    $validator->errors()->add('slot_positions', 'Data slot foto tidak valid.');

                    return;
                }

                foreach (['x', 'y', 'width', 'height'] as $key) {
                    if (! isset($slot[$key]) || ! is_numeric($slot[$key])) {
                        $validator->errors()->add(
                            'slot_positions',
                            'Semua slot foto harus punya koordinat yang valid.',
                        );

                        return;
                    }
                }

                if ((float) $slot['width'] <= 0 || (float) $slot['height'] <= 0) {
                    $validator->errors()->add(
                        'slot_positions',
                        'Ukuran setiap slot foto harus lebih besar dari 0.',
                    );

                    return;
                }
            }
        });
    }
}
