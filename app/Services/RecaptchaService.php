<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaService
{
    /**
     * Verify a reCAPTCHA v3 token against Google.
     */
    public function verify(string $token, ?string $secretKey = null, float $minScore = 0.5): bool
    {
        if ($this->shouldBypass()) {
            return true;
        }

        $secretKey ??= (string) Setting::get('recaptcha_secret_key');

        if ($token === '' || $secretKey === '') {
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout(10)
                ->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => $secretKey,
                    'response' => $token,
                    'remoteip' => request()->ip(),
                ]);

            if (! $response->successful()) {
                Log::warning('reCAPTCHA verification request failed', ['status' => $response->status()]);

                return false;
            }

            $data = $response->json();

            return ($data['success'] ?? false) === true
                && isset($data['score'])
                && $data['score'] >= $minScore;
        } catch (\Throwable $e) {
            Log::error('reCAPTCHA verification error', ['error' => $e->getMessage()]);

            return false;
        }
    }

    public function isEnabled(): bool
    {
        if ($this->shouldBypass()) {
            return false;
        }

        return Setting::get('recaptcha_enabled', '0') === '1';
    }

    public function siteKey(): ?string
    {
        $key = (string) Setting::get('recaptcha_site_key');

        return $key !== '' ? $key : null;
    }

    private function shouldBypass(): bool
    {
        if (app()->environment(['local', 'testing'])) {
            return true;
        }

        $host = request()->getHost();

        return in_array($host, ['localhost', '127.0.0.1', '::1'], true);
    }
}
