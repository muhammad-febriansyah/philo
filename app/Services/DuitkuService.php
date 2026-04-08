<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DuitkuService
{
    private string $merchantCode;

    private string $apiKey;

    private bool $sandbox;

    private string $baseUrl;

    private string $paymentMethod;

    public function __construct()
    {
        $this->merchantCode = Setting::get('duitku_merchant_code', '');
        $this->apiKey = Setting::get('duitku_api_key', '');
        $this->sandbox = Setting::get('duitku_is_sandbox', '1') === '1';
        $this->paymentMethod = 'GQ';
        $this->baseUrl = $this->sandbox
            ? 'https://sandbox.duitku.com/webapi'
            : 'https://passport.duitku.com/webapi';
    }

    /**
     * Create a QRIS transaction in Duitku.
     *
     * @return array{reference: string, qr_string: string, payment_url: string, expired_at: string}
     *
     * @throws \RuntimeException
     */
    public function createTransaction(
        string $orderId,
        int $amount,
        string $productDetail,
        string $email,
        string $customerName,
        string $callbackUrl,
        string $returnUrl,
        int $expiryMinutes = 15,
    ): array {
        if (empty($this->merchantCode) || empty($this->apiKey)) {
            throw new \RuntimeException('Duitku belum dikonfigurasi di pengaturan pembayaran.');
        }

        $signature = md5($this->merchantCode . $orderId . $amount . $this->apiKey);

        $payload = [
            'merchantCode' => $this->merchantCode,
            'paymentAmount' => $amount,
            'paymentMethod' => $this->paymentMethod,
            'merchantOrderId' => $orderId,
            'productDetails' => $productDetail,
            'email' => $email,
            'customerVaName' => $customerName,
            'callbackUrl' => $callbackUrl,
            'returnUrl' => $returnUrl,
            'signature' => $signature,
            'expiryPeriod' => $expiryMinutes,
        ];

        $response = Http::timeout(30)
            ->post($this->baseUrl . '/api/merchant/v2/inquiry', $payload);

        if (! $response->successful()) {
            Log::error('Duitku createTransaction failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'order_id' => $orderId,
            ]);
            throw new \RuntimeException('Gagal membuat transaksi Duitku: ' . ($response->json('Message') ?? $response->body()));
        }

        $data = $response->json();

        return [
            'reference' => $data['reference'],
            'qr_string' => $data['qrString'] ?? '',
            'payment_url' => $data['paymentUrl'] ?? '',
            'expired_at' => now()->addMinutes($expiryMinutes)->toISOString(),
        ];
    }

    /**
     * Check transaction status from Duitku.
     */
    public function checkTransaction(string $orderId): string
    {
        $signature = md5($this->merchantCode . $orderId . $this->apiKey);

        $response = Http::timeout(15)
            ->post($this->baseUrl . '/api/merchant/transactionStatus', [
                'merchantCode' => $this->merchantCode,
                'merchantOrderId' => $orderId,
                'signature' => $signature,
            ]);

        if (! $response->successful()) {
            Log::warning('Duitku checkTransaction failed', [
                'order_id' => $orderId,
                'status' => $response->status(),
            ]);

            return 'pending';
        }

        $statusCode = $response->json('statusCode');

        return match ($statusCode) {
            '00' => 'paid',
            '02' => 'cancelled',
            default => 'pending',
        };
    }

    /**
     * Verify callback signature from Duitku.
     */
    public function verifyCallbackSignature(
        string $amount,
        string $orderId,
        string $signature,
    ): bool {
        $expected = md5($this->merchantCode . $amount . $orderId . $this->apiKey);

        return hash_equals($expected, $signature);
    }

    public function isSandbox(): bool
    {
        return $this->sandbox;
    }
}
