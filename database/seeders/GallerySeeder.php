<?php

namespace Database\Seeders;

use App\Models\Gallery;
use Illuminate\Database\Seeder;

class GallerySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['title' => 'Wedding Romantis di Bali',     'description' => 'Momen pernikahan yang penuh kebahagiaan di tepi pantai Bali.'],
            ['title' => 'Prewedding Outdoor',           'description' => 'Sesi foto prewedding di taman kota dengan latar hijau yang asri.'],
            ['title' => 'Wisuda Universitas Indonesia', 'description' => 'Kenangan wisuda yang tak terlupakan bersama keluarga tercinta.'],
            ['title' => 'Portrait Studio Keluarga',     'description' => 'Sesi foto keluarga hangat di studio dengan pencahayaan profesional.'],
            ['title' => 'Ulang Tahun Si Kecil',         'description' => 'Momen ulang tahun anak yang ceria dan penuh warna.'],
            ['title' => 'Corporate Event Tahunan',      'description' => 'Dokumentasi acara perusahaan dengan suasana profesional.'],
            ['title' => 'Sweet Seventeen',              'description' => 'Perayaan ulang tahun ke-17 yang berkesan dan penuh kenangan.'],
            ['title' => 'Reunion Kelas SMA',            'description' => 'Reunian bersama teman-teman lama yang penuh tawa dan cerita.'],
            ['title' => 'Foto Produk Fashion',          'description' => 'Sesi pemotretan koleksi busana terbaru untuk katalog brand.'],
            ['title' => 'Birthday Party Surprise',      'description' => 'Pesta ulang tahun kejutan yang berhasil membuat bahagia.'],
        ];

        $images = collect(glob(storage_path('app/public/galleries/*.{jpg,jpeg,png,webp}'), GLOB_BRACE))
            ->map(fn ($f) => 'galleries/'.basename($f))
            ->values();

        foreach ($items as $i => $item) {
            Gallery::create([
                'title' => $item['title'],
                'description' => $item['description'],
                'image_path' => $images->get($i, ''),
                'sort_order' => $i + 1,
                'is_active' => true,
            ]);
        }
    }
}
