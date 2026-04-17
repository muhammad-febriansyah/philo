<?php

namespace Database\Seeders;

use App\Models\Step;
use Illuminate\Database\Seeder;

class StepSeeder extends Seeder
{
    public function run(): void
    {
        $steps = [
            [
                'number' => '01',
                'title' => 'Pilih Paket & Cabang',
                'description' => 'Kunjungi cabang Philo terdekat, pilih paket foto yang sesuai kebutuhan, lalu lakukan pembayaran melalui QRIS di mesin kami.',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'number' => '02',
                'title' => 'Pilih Template',
                'description' => 'Pilih template frame favoritmu dari berbagai koleksi desain eksklusif yang tersedia di layar booth.',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'number' => '03',
                'title' => 'Mulai Sesi Foto',
                'description' => 'Berpose dengan gaya terbaikmu! Booth akan menghitung mundur sebelum setiap jepretan secara otomatis.',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'number' => '04',
                'title' => 'Cetak & Bawa Pulang',
                'description' => 'Dalam hitungan detik, foto langsung dicetak dengan kualitas tinggi dan siap kamu bawa pulang sebagai kenangan.',
                'sort_order' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($steps as $step) {
            Step::create($step);
        }
    }
}
