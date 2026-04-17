<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DokuService
{
    private string $clientId;

    private string $secretKey;

    private string $privateKeyPem;

    private string $merchantId;

    private string $terminalId;

    private bool $sandbox;

    private string $baseUrl;

    public function __construct()
    {
        $this->clientId = Setting::get('doku_client_id', '');
        $this->secretKey = Setting::get('doku_secret_key', '');
        $this->merchantId = Setting::get('doku_merchant_id', $this->clientId);
        $this->terminalId = Setting::get('doku_terminal_id', 'BOOTH001');
        $this->sandbox = Setting::get('doku_is_sandbox', '1') === '1';
        $this->baseUrl = $this->sandbox
            ? 'https://api-sandbox.doku.com'
            : 'https://api.doku.com';

        $b64 = env('DOKU_PRIVATE_KEY_BASE64', '');
        $this->privateKeyPem = $b64 ? base64_decode($b64) : '';
    }

    /**
     * Get B2B access token via RSA SHA256withRSA signature.
     */
    private function getAccessToken(): string
    {
        $cacheKey = 'doku_access_token_'.md5($this->clientId);

        return Cache::remember($cacheKey, 840, function () {
            $timestamp = now()->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i:sP');
            $stringToSign = $this->clientId.'|'.$timestamp;

            $privateKey = openssl_pkey_get_private($this->privateKeyPem);

            if (! $privateKey) {
                throw new \RuntimeException('DOKU private key tidak valid. Pastikan DOKU_PRIVATE_KEY_BASE64 sudah diisi di .env');
            }

            openssl_sign($stringToSign, $rawSignature, $privateKey, OPENSSL_ALGO_SHA256);
            $signature = base64_encode($rawSignature);

            $response = Http::timeout(15)
                ->withHeaders([
                    'X-CLIENT-KEY' => $this->clientId,
                    'X-TIMESTAMP' => $timestamp,
                    'X-SIGNATURE' => $signature,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->baseUrl.'/authorization/v1/access-token/b2b', [
                    'grantType' => 'client_credentials',
                ]);

            if (! $response->successful()) {
                Log::error('DOKU getAccessToken failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \RuntimeException('Gagal mendapatkan access token DOKU: '.$response->body());
            }

            return $response->json('accessToken');
        });
    }

    /**
     * Build SNAP service headers with HMAC-SHA512 signature.
     *
     * @return array<string, string>
     */
    private function buildServiceHeaders(
        string $method,
        string $path,
        string $accessToken,
        string $bodyJson,
        string $timestamp,
    ): array {
        $bodyHash = strtolower(bin2hex(hash('sha256', $bodyJson, true)));
        $stringToSign = strtoupper($method).':'.$path.':'.$accessToken.':'.$bodyHash.':'.$timestamp;

        $signature = base64_encode(hash_hmac('sha512', $stringToSign, $this->secretKey, true));

        return [
            'X-PARTNER-ID' => $this->clientId,
            'X-EXTERNAL-ID' => substr(str_replace('-', '', Str::uuid()->toString()), 0, 36),
            'X-TIMESTAMP' => $timestamp,
            'X-SIGNATURE' => $signature,
            'Authorization' => 'Bearer '.$accessToken,
            'CHANNEL-ID' => 'H2H',
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Create a dynamic QRIS transaction.
     *
     * @return array{qr_string: string, expired_at: string}
     *
     * @throws \RuntimeException
     */
    public function createQrisTransaction(
        string $orderId,
        int $amount,
        string $productDetail,
        string $email,
        string $customerName,
        string $callbackUrl,
        int $expiryMinutes = 15,
    ): array {
        if (empty($this->clientId) || empty($this->secretKey)) {
            throw new \RuntimeException('DOKU belum dikonfigurasi di pengaturan pembayaran.');
        }

        $accessToken = $this->getAccessToken();
        $path = '/snap-adapter/b2b/v1.0/qr/qr-mpm-generate';
        $timestamp = now()->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i:sP');
        $validityPeriod = now()->addMinutes($expiryMinutes)->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i:sP');

        $body = [
            'partnerReferenceNo' => $orderId,
            'amount' => [
                'value' => number_format($amount, 2, '.', ''),
                'currency' => 'IDR',
            ],
            'merchantId' => $this->merchantId,
            'terminalId' => $this->terminalId,
            'validityPeriod' => $validityPeriod,
            'additionalInfo' => [
                'postalCode' => '00000',
                'feeType' => 1,
            ],
        ];

        $bodyJson = (string) json_encode($body);
        $headers = $this->buildServiceHeaders('POST', $path, $accessToken, $bodyJson, $timestamp);

        $response = Http::timeout(30)
            ->withHeaders($headers)
            ->post($this->baseUrl.$path, $body);

        if (! $response->successful()) {
            Log::error('DOKU createQrisTransaction failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'order_id' => $orderId,
            ]);
            throw new \RuntimeException(
                'Gagal membuat transaksi DOKU: '.($response->json('responseMessage') ?? $response->body())
            );
        }

        $qrContent = $response->json('qrContent') ?? '';

        if (empty($qrContent)) {
            Log::error('DOKU QRIS qrContent empty', ['response' => $response->json()]);
            throw new \RuntimeException('DOKU tidak mengembalikan QR content.');
        }

        return [
            'qr_string' => $qrContent,
            'expired_at' => now()->addMinutes($expiryMinutes)->toISOString(),
        ];
    }

    /**
     * Check transaction status from DOKU.
     *
     * @return 'paid'|'failed'|'pending'
     */
    public function checkTransaction(string $orderId): string
    {
        try {
            $accessToken = $this->getAccessToken();
        } catch (\RuntimeException) {
            return 'pending';
        }

        $path = '/snap-adapter/b2b/v1.0/qr/qr-mpm-query';
        $timestamp = now()->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i:sP');

        $body = [
            'originalPartnerReferenceNo' => $orderId,
            'merchantId' => $this->merchantId,
            'serviceCode' => '47',
        ];

        $bodyJson = (string) json_encode($body);
        $headers = $this->buildServiceHeaders('POST', $path, $accessToken, $bodyJson, $timestamp);

        $response = Http::timeout(15)
            ->withHeaders($headers)
            ->post($this->baseUrl.$path, $body);

        if (! $response->successful()) {
            Log::warning('DOKU checkTransaction failed', [
                'order_id' => $orderId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return 'pending';
        }

        $status = $response->json('latestTransactionStatus');

        return match ($status) {
            '00' => 'paid',
            '05' => 'failed',
            default => 'pending',
        };
    }

    /**
     * Verify DOKU notification callback signature.
     */
    public function verifyCallbackSignature(
        string $clientId,
        string $requestId,
        string $requestTimestamp,
        string $requestTarget,
        string $requestBody,
        string $signature,
    ): bool {
        // SNAP callback uses HMAC-SHA512 with same string format as service headers
        $bodyHash = strtolower(bin2hex(hash('sha256', $requestBody, true)));
        $stringToSign = 'POST:'.$requestTarget.':'.$requestId.':'.$bodyHash.':'.$requestTimestamp;

        $expected = base64_encode(hash_hmac('sha512', $stringToSign, $this->secretKey, true));

        return hash_equals($expected, $signature);
    }

    public function isSandbox(): bool
    {
        return $this->sandbox;
    }
}
