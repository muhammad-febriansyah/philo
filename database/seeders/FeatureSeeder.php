<?php

namespace Database\Seeders;

use App\Models\Feature;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    public function run(): void
    {
        $features = [
            [
                'icon' => 'mdi mdi-camera-iris',
                'title' => 'Foto Cetak Instan',
                'description' => 'Hasil foto langsung tercetak dalam hitungan detik begitu sesi selesai. Bisa langsung dibawa pulang sebagai kenangan fisik tanpa perlu menunggu.',
            ],
            [
                'icon' => 'mdi mdi-image-multiple',
                'title' => 'Banyak Pilihan Frame',
                'description' => 'Ratusan template frame tematik—mulai dari minimalis, vintage, pastel, sampai edisi spesial event—siap dipilih sesuai mood foto kamu.',
            ],
            [
                'icon' => 'mdi mdi-qrcode-scan',
                'title' => 'Pembayaran QRIS',
                'description' => 'Bayar cukup scan QRIS lewat aplikasi e-wallet atau mobile banking favoritmu. Cepat, tanpa kembalian, tanpa antri kasir.',
            ],
            [
                'icon' => 'mdi mdi-storefront',
                'title' => 'Tersedia di Banyak Cabang',
                'description' => 'Booth Philo hadir di berbagai cabang strategis. Datang ke booth terdekat, pengalaman foto yang sama berkualitas di mana pun kamu berada.',
            ],
            [
                'icon' => 'mdi mdi-auto-fix',
                'title' => 'Filter & Editor Foto',
                'description' => 'Pilihan filter cinematic, beauty, vintage, B&W, dan lainnya untuk mempercantik hasil foto. Bisa diedit sebelum dicetak.',
            ],
            [
                'icon' => 'mdi mdi-cellphone-link',
                'title' => 'Download via QR Code',
                'description' => 'Selain cetak fisik, hasil foto bisa langsung disimpan ke HP dengan scan QR. Praktis untuk dishare ke media sosial.',
            ],
            [
                'icon' => 'mdi mdi-printer-pos',
                'title' => 'Multi Ukuran Cetak',
                'description' => 'Pilih ukuran cetak sesuai kebutuhan—Photo Strip 2"×6", 4R, A4, atau A3. Cocok untuk dipajang, scrapbook, atau dibagikan.',
            ],
            [
                'icon' => 'mdi mdi-clock-fast',
                'title' => 'Proses 10–15 Menit',
                'description' => 'Dari pilih paket sampai foto siap dibawa pulang hanya butuh 10–15 menit. Cepat, simpel, tanpa drama teknis.',
            ],
            [
                'icon' => 'mdi mdi-account-group-outline',
                'title' => 'Cocok untuk Grup',
                'description' => 'Frame dengan banyak slot foto memungkinkan booth dipakai bareng teman, keluarga, atau pasangan. Maksimal kebersamaan dalam satu cetakan.',
            ],
            [
                'icon' => 'mdi mdi-shield-check-outline',
                'title' => 'Operator Stand By',
                'description' => 'Tim operator setiap cabang siap membantu kalau ada kendala teknis—dari setup pose, masalah cetak, sampai pertanyaan paket.',
            ],
        ];

        Feature::query()->delete();

        foreach ($features as $index => $feature) {
            Feature::create([
                ...$feature,
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);
        }
    }
}
