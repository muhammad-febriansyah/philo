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
        $settings = [
            // Branding
            'site_name' => 'Philo Photobooth',
            'site_description' => 'Photobooth Multi Cabang',
            'logo_path' => null,
            'favicon_path' => null,
            'primary_color' => '#6366f1',

            // Duitku
            'duitku_merchant_code' => null,
            'duitku_api_key' => null,
            'duitku_callback_url' => null,
            'duitku_is_sandbox' => '1',

            // Booth
            'booth_countdown_seconds' => '3',
            'booth_idle_timeout_seconds' => '60',
            'booth_watermark_path' => null,

            // Print
            'print_enabled' => '1',
            'print_dpi' => '300',
            'print_default_size' => 'A3',

            // Social / Footer
            'instagram_url' => null,
            'tiktok_url' => null,
            'whatsapp_number' => null,
            'footer_text' => '© Philo Photobooth',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
