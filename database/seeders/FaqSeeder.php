<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'question' => 'Apakah harga paket sudah termasuk cetak foto?',
                'answer' => 'Ya, semua paket sudah termasuk cetak foto langsung di booth sesuai jumlah foto pada paket yang dipilih.',
            ],
            [
                'question' => 'Metode pembayaran apa yang tersedia?',
                'answer' => 'Kami mendukung pembayaran QRIS sehingga pelanggan bisa membayar lewat e-wallet, mobile banking, dan aplikasi pembayaran lain yang kompatibel.',
            ],
            [
                'question' => 'Berapa lama durasi satu sesi foto?',
                'answer' => 'Rata-rata sesi foto berlangsung sekitar 10 sampai 15 menit, termasuk pemilihan template dan proses cetak.',
            ],
            [
                'question' => 'Apakah pelanggan bisa memilih frame atau template sendiri?',
                'answer' => 'Bisa. Pelanggan dapat memilih template yang tersedia sesuai paket dan ukuran cetak yang aktif di cabang tersebut.',
            ],
            [
                'question' => 'Apakah hasil foto bisa dikirim ke email?',
                'answer' => 'Bisa, jika fitur pengiriman email diaktifkan pada sesi photobooth. Hasil digital akan dikirim setelah sesi selesai.',
            ],
            [
                'question' => 'Apakah bisa foto rame-rame dalam satu sesi?',
                'answer' => 'Bisa. Jumlah orang yang muat menyesuaikan ukuran frame, jarak kamera, dan layout template yang dipilih.',
            ],
            [
                'question' => 'Kalau pembayaran gagal, apakah transaksi bisa diulang?',
                'answer' => 'Bisa. Operator atau pelanggan dapat membuat transaksi baru jika transaksi sebelumnya gagal, expired, atau dibatalkan.',
            ],
            [
                'question' => 'Apakah cabang bisa punya paket dan template yang berbeda?',
                'answer' => 'Bisa. Admin dapat mengatur paket aktif, template, dan penawaran yang dipakai pada masing-masing cabang sesuai kebutuhan.',
            ],
            [
                'question' => 'Apakah foto yang sudah selesai disimpan di sistem?',
                'answer' => 'Ya, sistem menyimpan data sesi dan file foto sesuai pengaturan penyimpanan yang digunakan oleh aplikasi.',
            ],
            [
                'question' => 'Bagaimana kalau printer atau kamera sedang bermasalah?',
                'answer' => 'Operator dapat mengecek transaksi, sesi foto, dan mengulangi proses sesuai kondisi di lokasi. Admin juga tetap bisa memantau dari dashboard.',
            ],
        ];

        foreach ($items as $index => $item) {
            Faq::firstOrCreate(
                ['question' => $item['question']],
                [
                    'answer' => $item['answer'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }
    }
}
