<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MailketingService
{
    private const ENDPOINT = 'https://api.mailketing.co.id/api/v1/send';

    /**
     * Send an email via Mailketing API.
     *
     * @param  array<int, string>  $attachments  URLs of optional attachments (max 3, each max 2MB)
     * @return array{success: bool, message: string, response?: array}
     */
    public function send(string $to, string $subject, string $content, array $attachments = []): array
    {
        if (! $this->isEnabled()) {
            return ['success' => false, 'message' => 'Notifikasi email dinonaktifkan dari pengaturan.'];
        }

        $config = $this->getConfig();

        if (empty($config['api_token']) || empty($config['from_email'])) {
            return ['success' => false, 'message' => 'Konfigurasi Mailketing belum lengkap (API token / email pengirim kosong).'];
        }

        $payload = [
            'api_token' => $config['api_token'],
            'from_name' => $config['from_name'] ?: 'Philo Photobooth',
            'from_email' => $config['from_email'],
            'recipient' => $to,
            'subject' => $subject,
            'content' => $content,
        ];

        foreach (array_slice(array_values($attachments), 0, 3) as $i => $url) {
            $payload['attach'.($i + 1)] = $url;
        }

        try {
            $response = Http::asForm()
                ->timeout(15)
                ->post(self::ENDPOINT, $payload);

            $data = $response->json() ?? [];
            $success = $response->ok() && (($data['status'] ?? null) === 'success');

            if (! $success) {
                Log::warning('Mailketing send failed', [
                    'to' => $to,
                    'subject' => $subject,
                    'status' => $response->status(),
                    'body' => $data,
                ]);
            }

            return [
                'success' => $success,
                'message' => $data['response'] ?? ($success ? 'Email terkirim.' : 'Gagal mengirim email.'),
                'response' => $data,
            ];
        } catch (\Throwable $e) {
            Log::error('Mailketing exception', ['error' => $e->getMessage(), 'to' => $to]);

            return ['success' => false, 'message' => 'Terjadi kesalahan saat mengirim email: '.$e->getMessage()];
        }
    }

    public function isEnabled(): bool
    {
        return Setting::get('email_notifications_enabled', '0') === '1';
    }

    public function notifyPaidEnabled(): bool
    {
        return Setting::get('email_notify_paid', '1') === '1';
    }

    public function notifySessionCompleteEnabled(): bool
    {
        return Setting::get('email_notify_session_complete', '1') === '1';
    }

    /**
     * @return array{api_token: ?string, from_name: ?string, from_email: ?string}
     */
    public function getConfig(): array
    {
        $config = Setting::getMany(['mailketing_api_token', 'mailketing_from_name', 'mailketing_from_email']);

        return [
            'api_token' => $config['mailketing_api_token'] ?? null,
            'from_name' => $config['mailketing_from_name'] ?? null,
            'from_email' => $config['mailketing_from_email'] ?? null,
        ];
    }
}
