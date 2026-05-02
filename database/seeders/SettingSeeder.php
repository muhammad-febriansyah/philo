<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $logoFiles = glob(storage_path('app/public/settings/*.{png,jpg,jpeg}'), GLOB_BRACE);
        $logoPath = ! empty($logoFiles) ? 'settings/'.basename($logoFiles[0]) : null;

        $aboutFiles = glob(storage_path('app/public/settings/about/*.{jpg,jpeg,png}'), GLOB_BRACE);
        $aboutPath = ! empty($aboutFiles) ? 'settings/about/'.basename($aboutFiles[0]) : null;

        $settings = [
            // Branding
            'site_name' => 'Philo Photobooth',
            'site_description' => 'Photobooth Multi Cabang',
            'logo_path' => $logoPath,
            'favicon_path' => null,

            // Payment provider
            'payment_provider' => 'doku',

            // DOKU
            'doku_client_id' => null,
            'doku_secret_key' => null,
            'doku_is_sandbox' => '1',
            'doku_merchant_id' => null,
            'doku_terminal_id' => 'BOOTH001',

            // Duitku
            'duitku_merchant_code' => null,
            'duitku_api_key' => null,
            'duitku_payment_method' => 'GQ',
            'duitku_is_sandbox' => '1',

            // Booth
            'booth_countdown_seconds' => '3',
            'booth_idle_timeout_seconds' => '60',
            'booth_watermark_path' => null,
            'booth_base_price' => '25000',
            'booth_extra_print_price' => '5000',
            'booth_max_extra_prints' => '5',

            // Print
            'print_enabled' => '1',
            'print_dpi' => '300',
            'print_default_size' => 'A3',

            // Email Notification (Mailketing)
            'email_notifications_enabled' => '0',
            'mailketing_api_token' => 'fa8bd42078ed424a1e2761492655638f',
            'mailketing_from_name' => 'Philo Photobooth',
            'mailketing_from_email' => 'noreply@philo.id',
            'email_notify_paid' => '1',
            'email_notify_session_complete' => '1',

            // Google reCAPTCHA v3
            'recaptcha_enabled' => '0',
            'recaptcha_site_key' => '6LeBya4aAAAAAJ3b8Hc0y7wsyA5EYD4IvMqKMtr2',
            'recaptcha_secret_key' => '6LeBya4aAAAAAKxhmMl8rrk5-Uj4T7H5PhpoPVoT',

            // Social / Footer
            'instagram_url' => 'https://instagram.com/philo.photobooth',
            'facebook_url' => 'https://facebook.com/philo.photobooth',
            'x_url' => 'https://x.com/philo_photobooth',
            'tiktok_url' => 'https://tiktok.com/@philo.photobooth',
            'whatsapp_number' => '6281212345678',
            'footer_text' => '© Philo Photobooth. Abadikan momen, cetak kenangan.',
            'google_maps_embed' => '<iframe src="https://www.google.com/maps?q=Tugu+Kujang+Bogor&output=embed" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>',

            // Tentang Kami
            'about_title' => 'Tentang Philo Photobooth',
            'about_tagline' => 'Abadikan Momen, Cetak Kenangan',
            'about_description' => 'Philo Photobooth hadir sejak 2020 dengan misi sederhana: memberikan pengalaman foto yang menyenangkan, berkualitas, dan terjangkau untuk semua kalangan. Kami menghadirkan booth foto modern dengan berbagai pilihan frame eksklusif di berbagai kota besar Indonesia. Setiap sesi dirancang agar mudah digunakan siapa saja — cukup pilih paket, ambil foto, dan bawa pulang kenangan indah dalam hitungan menit.',
            'about_vision' => 'Menjadi jaringan photobooth multi-cabang terpercaya di Indonesia yang menghadirkan kenangan tak terlupakan bagi setiap pelanggan.',
            'about_mission' => "Menyediakan layanan foto berkualitas tinggi dengan harga yang terjangkau.\nMenghadirkan pengalaman yang menyenangkan dan mudah di setiap cabang.\nBerinovasi secara berkelanjutan dalam teknologi dan desain photobooth.\nMemperluas jangkauan ke kota-kota baru demi lebih banyak momen berharga.",
            'about_founded_year' => '2020',
            'about_total_sessions' => '10.000+',
            'about_total_branches' => '10+',
            'about_total_clients' => '8.500+',
            'about_hero_image_path' => $aboutPath,
        ];

        foreach ($settings as $key => $value) {
            $current = Setting::where('key', $key)->first();

            if (! $current) {
                Setting::create([
                    'key' => $key,
                    'value' => $value,
                ]);

                continue;
            }

            if ($current->value === null || trim((string) $current->value) === '') {
                $current->update(['value' => $value]);
            }
        }
    }
}
