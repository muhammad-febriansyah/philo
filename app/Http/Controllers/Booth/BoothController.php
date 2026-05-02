<?php

namespace App\Http\Controllers\Booth;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Gallery;
use App\Models\Package;
use App\Models\Photo;
use App\Models\PhotoSession;
use App\Models\Setting;
use App\Models\Template;
use App\Models\Transaction;
use App\Models\Voucher;
use App\Services\DokuService;
use App\Services\DuitkuService;
use App\Services\MailketingService;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class BoothController extends Controller
{
    public function show(Branch $branch): Response
    {
        abort_if(! $branch->is_active, 404, 'Booth ini tidak aktif.');

        $templates = Template::where('is_active', true)->get();
        $settings = Setting::getMany([
            'site_name',
            'logo_path',
            'booth_countdown_seconds',
            'booth_idle_timeout_seconds',
            'booth_base_price',
            'booth_extra_print_price',
            'booth_max_extra_prints',
            'payment_provider',
            'duitku_payment_method',
            'manual_qris_image_path',
            'print_enabled',
            'print_auto_print',
            'print_default_size',
        ]);

        $galleryImages = Gallery::where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('image_path')
            ->map(fn ($path) => Storage::url($path))
            ->values()
            ->toArray();

        return Inertia::render('booth/show', [
            'branch' => $branch->only('id', 'name', 'code', 'photo'),
            'galleryImages' => $galleryImages,
            'templates' => $templates->map(fn (Template $t) => [
                'id' => $t->id,
                'name' => $t->name,
                'thumbnail_path' => $t->thumbnail_path ? Storage::url($t->thumbnail_path) : null,
                'frame_path' => $t->frame_path ? Storage::url($t->frame_path) : null,
                'photo_slots' => $t->photo_slots,
                'slot_positions' => $t->slot_positions,
                'print_size' => $t->print_size,
            ]),
            'settings' => [
                'site_name' => $settings['site_name'],
                'logo_path' => $settings['logo_path'],
                'booth_countdown_seconds' => (int) ($settings['booth_countdown_seconds'] ?: 5),
                'booth_idle_timeout_seconds' => (int) ($settings['booth_idle_timeout_seconds'] ?: 60),
                'booth_base_price' => (int) ($settings['booth_base_price'] ?: 25000),
                'booth_extra_print_price' => (int) ($settings['booth_extra_print_price'] ?: 5000),
                'booth_max_extra_prints' => (int) ($settings['booth_max_extra_prints'] ?? 5),
                'payment_provider' => (string) ($settings['payment_provider'] ?: 'doku'),
                'duitku_payment_method' => strtoupper((string) ($settings['duitku_payment_method'] ?: 'GQ')),
                'manual_qris_image_url' => $settings['manual_qris_image_path']
                    ? Storage::url($settings['manual_qris_image_path'])
                    : null,
                'print_enabled' => (bool) ($settings['print_enabled'] ?? false),
                'print_auto_print' => (bool) ($settings['print_auto_print'] ?? false),
                'print_default_size' => (string) ($settings['print_default_size'] ?: 'A4'),
            ],
        ]);
    }

    /**
     * Resolves the internal default package used for booth sessions.
     * All packages now share the same price (controlled via settings); this
     * just provides a row to anchor transactions/photo_sessions to.
     */
    private function defaultPackage(): Package
    {
        return Package::where('is_active', true)
            ->orderBy('id')
            ->firstOrFail();
    }

    /**
     * Computes the booth charge from settings + extras.
     *
     * @return array{base:int, extra_unit:int, extra_count:int, max_extra:int, total:int}
     */
    private function computeBoothCharge(int $extraPrints): array
    {
        $base = (int) Setting::get('booth_base_price', 25000);
        $extraUnit = (int) Setting::get('booth_extra_print_price', 5000);
        $maxExtra = (int) Setting::get('booth_max_extra_prints', 5);
        $extraCount = max(0, min($extraPrints, $maxExtra));

        return [
            'base' => $base,
            'extra_unit' => $extraUnit,
            'extra_count' => $extraCount,
            'max_extra' => $maxExtra,
            'total' => $base + ($extraUnit * $extraCount),
        ];
    }

    public function validateVoucher(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:64'],
            'branch_id' => ['required', 'exists:branches,id'],
            'extra_prints' => ['nullable', 'integer', 'min:0', 'max:99'],
        ]);

        $voucher = Voucher::where('code', strtoupper(trim($validated['code'])))->first();

        if (! $voucher) {
            return response()->json(['valid' => false, 'message' => 'Kode voucher tidak ditemukan.'], 200);
        }

        $package = $this->defaultPackage();
        $charge = $this->computeBoothCharge((int) ($validated['extra_prints'] ?? 0));
        $totalAmount = $charge['total'];

        if (! $voucher->isUsableFor((int) $package->id, (int) $validated['branch_id'], (float) $totalAmount)) {
            $reason = match (true) {
                ! $voucher->is_active => 'Voucher tidak aktif.',
                $voucher->isExpired() => 'Voucher sudah kedaluwarsa.',
                $voucher->isNotYetActive() => 'Voucher belum berlaku.',
                $voucher->isExhausted() => 'Voucher sudah habis dipakai.',
                $voucher->min_purchase && (float) $totalAmount < (float) $voucher->min_purchase => 'Minimum pembelian Rp '.number_format((float) $voucher->min_purchase, 0, ',', '.').'.',
                ! empty($voucher->applicable_packages) && ! in_array($package->id, $voucher->applicable_packages) => 'Voucher tidak berlaku untuk paket ini.',
                ! empty($voucher->applicable_branches) && ! in_array((int) $validated['branch_id'], $voucher->applicable_branches) => 'Voucher tidak berlaku di cabang ini.',
                default => 'Voucher tidak dapat digunakan.',
            };

            return response()->json(['valid' => false, 'message' => $reason], 200);
        }

        $discount = (int) round($voucher->calculateDiscount((float) $totalAmount));
        $finalAmount = max(0, $totalAmount - $discount);

        return response()->json([
            'valid' => true,
            'voucher' => [
                'id' => $voucher->id,
                'code' => $voucher->code,
                'name' => $voucher->name,
                'type' => $voucher->type,
                'value' => (float) $voucher->value,
            ],
            'original_amount' => $totalAmount,
            'discount_amount' => $discount,
            'final_amount' => $finalAmount,
            'message' => 'Voucher diterapkan: hemat Rp '.number_format($discount, 0, ',', '.').'.',
        ]);
    }

    public function startSession(Request $request, DokuService $doku, DuitkuService $duitku): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'extra_prints' => ['nullable', 'integer', 'min:0', 'max:99'],
            'payment_method_code' => ['nullable', 'string', 'max:20'],
            'voucher_code' => ['nullable', 'string', 'max:64'],
        ]);

        $package = $this->defaultPackage();
        $charge = $this->computeBoothCharge((int) ($validated['extra_prints'] ?? 0));
        $extraCount = $charge['extra_count'];
        $originalAmount = $charge['total'];

        $orderId = 'PHILO-'.strtoupper(Str::random(6)).'-'.now()->format('YmdHis');

        $voucherId = null;
        $discountAmount = 0;
        $amount = $originalAmount;

        if (! empty($validated['voucher_code'])) {
            $voucher = Voucher::where('code', strtoupper(trim($validated['voucher_code'])))->first();

            if ($voucher && $voucher->isUsableFor((int) $package->id, (int) $validated['branch_id'], (float) $originalAmount)) {
                $discountAmount = (int) round($voucher->calculateDiscount((float) $originalAmount));
                $amount = max(0, $originalAmount - $discountAmount);
                $voucherId = $voucher->id;
            }
        }

        $productDetail = 'Foto Booth - Sesi'.($extraCount > 0 ? ' + '.$extraCount.' cetak tambahan' : '');

        $transaction = Transaction::create([
            'order_id' => $orderId,
            'branch_id' => $validated['branch_id'],
            'package_id' => $package->id,
            'extra_prints' => $extraCount,
            'voucher_id' => $voucherId,
            'amount' => $amount,
            'discount_amount' => $discountAmount,
            'original_amount' => $voucherId ? $originalAmount : null,
            'payment_method' => $this->activeProvider(),
            'status' => 'pending',
            'expired_at' => now()->addMinutes(15),
        ]);

        $provider = $transaction->payment_method;

        if ($provider === 'manual') {
            $manualQrisImagePath = (string) Setting::get('manual_qris_image_path', '');
            $manualQrisImageUrl = $manualQrisImagePath ? Storage::url($manualQrisImagePath) : null;

            return response()->json([
                'transaction_id' => $transaction->id,
                'order_id' => $transaction->order_id,
                'amount' => $transaction->amount,
                'qr_url' => null,
                'expired_at' => now()->addMinutes(15)->toISOString(),
                'payment_provider' => 'manual',
                'payment_method_code' => null,
                'gateway_reference' => null,
                'payment_url' => null,
                'is_simulation' => false,
                'manual_qris_image_url' => $manualQrisImageUrl,
            ]);
        }

        $publicBaseUrl = $request->getSchemeAndHttpHost();
        $callbackPath = route('api.booth.payment.callback', [], false);
        $callbackUrl = $publicBaseUrl.$callbackPath;

        try {
            $resolvedPaymentMethod = null;
            if ($provider === 'duitku') {
                $branchCode = Branch::findOrFail($validated['branch_id'])->code;
                $requestedMethod = strtoupper(trim((string) ($validated['payment_method_code'] ?? '')));
                $paymentMethod = $requestedMethod !== ''
                    ? $requestedMethod
                    : strtoupper((string) Setting::get('duitku_payment_method', 'GQ'));
                $resolvedPaymentMethod = $paymentMethod;
                $returnPath = route('booth.show', [
                    'branch' => $branchCode,
                    'gateway_return' => 'duitku',
                    'tx' => $transaction->id,
                ], false);
                $returnUrl = $publicBaseUrl.$returnPath;

                $result = $duitku->createTransaction(
                    orderId: $orderId,
                    amount: $amount,
                    productDetail: $productDetail,
                    email: 'customer@philobooth.com',
                    customerName: 'Pelanggan Philo',
                    callbackUrl: $callbackUrl,
                    returnUrl: $returnUrl,
                    paymentMethod: $paymentMethod,
                    expiryMinutes: 15,
                );
            } else {
                $result = $doku->createQrisTransaction(
                    orderId: $orderId,
                    amount: $amount,
                    productDetail: $productDetail,
                    email: 'customer@philobooth.com',
                    customerName: 'Pelanggan Philo',
                    callbackUrl: $callbackUrl,
                    expiryMinutes: 15,
                );
            }

            $transaction->update([
                'duitku_reference' => $result['reference'] ?? $orderId,
                'payment_url' => $result['payment_url'] ?? null,
                'qris_string' => $result['qr_string'],
                'expired_at' => $result['expired_at'],
            ]);

            $qrUrl = ! empty($result['qr_string'])
                ? 'https://api.qrserver.com/v1/create-qr-code/?size=280x280&data='.urlencode($result['qr_string'])
                : null;
            $expiredAt = $result['expired_at'];
        } catch (\RuntimeException $e) {
            Log::warning(strtoupper($provider).' fallback to simulation', ['error' => $e->getMessage()]);
            $transaction->update(['qris_string' => $orderId]);
            $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=280x280&data='.urlencode($orderId);
            $expiredAt = now()->addMinutes(15)->toISOString();
        }

        return response()->json([
            'transaction_id' => $transaction->id,
            'order_id' => $transaction->order_id,
            'amount' => $transaction->amount,
            'qr_url' => $qrUrl,
            'expired_at' => $expiredAt,
            'payment_provider' => $provider,
            'payment_method_code' => $resolvedPaymentMethod,
            'gateway_reference' => $transaction->duitku_reference,
            'payment_url' => $transaction->payment_url,
            'is_simulation' => $transaction->qris_string === $orderId,
        ]);
    }

    /**
     * Re-issues a fresh transaction (cancelling the previous pending one)
     * with the supplied voucher applied. Used when customer enters a voucher
     * after the QR has already been generated on the payment screen.
     */
    public function reissueSession(Request $request, DokuService $doku, DuitkuService $duitku): JsonResponse
    {
        $validated = $request->validate([
            'transaction_id' => ['required', 'integer', 'exists:transactions,id'],
            'voucher_code' => ['nullable', 'string', 'max:64'],
        ]);

        $previous = Transaction::findOrFail($validated['transaction_id']);

        abort_if($previous->isPaid(), 422, 'Transaksi sudah dibayar, tidak bisa diubah.');

        // Mark old transaction as cancelled so polling on the old QR stops.
        $previous->update(['status' => 'cancelled']);

        $request->merge([
            'branch_id' => $previous->branch_id,
            'extra_prints' => (int) ($previous->extra_prints ?? 0),
            'voucher_code' => $validated['voucher_code'] ?? null,
            'payment_method_code' => null,
        ]);

        return $this->startSession($request, $doku, $duitku);
    }

    public function checkPayment(Transaction $transaction, DokuService $doku, DuitkuService $duitku): JsonResponse
    {
        if ($transaction->isPaid()) {
            return response()->json(['status' => $transaction->status, 'paid' => true]);
        }

        if ($transaction->duitku_reference) {
            try {
                $provider = $this->resolveProviderForTransaction($transaction);
                $status = $provider === 'duitku'
                    ? $duitku->checkTransaction($transaction->order_id)
                    : $doku->checkTransaction($transaction->order_id);

                if ($status === 'paid') {
                    $transaction->markAsPaid();
                } elseif (in_array($status, ['failed', 'cancelled'], true)) {
                    $transaction->update(['status' => 'failed']);
                }

                $transaction->refresh();
            } catch (\RuntimeException $e) {
                Log::warning(strtoupper($this->resolveProviderForTransaction($transaction)).' checkTransaction failed', ['error' => $e->getMessage()]);
            }
        }

        return response()->json([
            'status' => $transaction->status,
            'paid' => $transaction->isPaid(),
        ]);
    }

    public function dokuCallback(Request $request): \Illuminate\Http\Response
    {
        $provider = $this->detectCallbackProvider($request);
        Log::info('Payment callback received', [
            'provider' => $provider,
            'path' => $request->path(),
            'ip' => $request->ip(),
        ]);

        if ($provider === 'duitku') {
            return $this->duitkuCallback($request);
        }

        return $this->processDokuCallback($request);
    }

    private function processDokuCallback(Request $request): \Illuminate\Http\Response
    {
        $rawBody = $request->getContent();
        $clientId = (string) $request->header('Client-Id', '');
        $requestId = (string) $request->header('Request-Id', '');
        $requestTimestamp = (string) $request->header('Request-Timestamp', '');
        $signature = (string) $request->header('Signature', '');
        $requestTarget = $request->getPathInfo();

        $doku = app(DokuService::class);

        if (! $doku->verifyCallbackSignature($clientId, $requestId, $requestTimestamp, $requestTarget, $rawBody, $signature)) {
            Log::warning('DOKU callback signature mismatch', [
                'client_id' => $clientId,
                'path' => $requestTarget,
            ]);
            abort(403, 'Invalid signature');
        }

        $orderId = $request->input('order.invoice_number');
        $status = $request->input('transaction.status');
        Log::info('DOKU callback payload parsed', [
            'order_id' => $orderId,
            'status' => $status,
        ]);

        if ($orderId && $status === 'SUCCESS') {
            $tx = Transaction::where('order_id', $orderId)->first();
            $tx?->markAsPaid();
        }

        return response()->noContent();
    }

    private function duitkuCallback(Request $request): \Illuminate\Http\Response
    {
        $orderId = (string) $request->input('merchantOrderId', '');
        $amount = (string) $request->input('amount', '');
        $signature = (string) $request->input('signature', '');
        $statusCode = (string) $request->input('resultCode', '');
        Log::info('Duitku callback payload parsed', [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'amount' => $amount,
        ]);

        $duitku = app(DuitkuService::class);

        if (! $duitku->verifyCallbackSignature($amount, $orderId, $signature)) {
            Log::warning('Duitku callback signature mismatch', [
                'order_id' => $orderId,
            ]);
            abort(403, 'Invalid signature');
        }

        if ($orderId && $statusCode === '00') {
            $tx = Transaction::where('order_id', $orderId)->first();
            $tx?->markAsPaid();
        } elseif ($orderId && $statusCode === '02') {
            Transaction::where('order_id', $orderId)->update([
                'status' => 'failed',
            ]);
        }

        return response()->noContent();
    }

    public function simulatePayment(Transaction $transaction): JsonResponse
    {
        abort_unless(app()->isLocal() || app()->environment('staging'), 403, 'Simulasi hanya tersedia di mode development.');

        $transaction->markAsPaid();

        return response()->json(['paid' => true]);
    }

    public function confirmManualPayment(Transaction $transaction): JsonResponse
    {
        abort_if($transaction->payment_method !== 'manual', 403, 'Hanya tersedia untuk pembayaran manual.');
        abort_if($transaction->isPaid(), 200, 'Transaksi sudah dibayar.');

        $transaction->markAsPaid();

        return response()->json(['paid' => true]);
    }

    public function createSession(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'transaction_id' => ['required', 'exists:transactions,id'],
        ]);

        $transaction = Transaction::with('package')->findOrFail($validated['transaction_id']);

        abort_if(! $transaction->isPaid(), 422, 'Transaksi belum dibayar.');

        $session = PhotoSession::create([
            'transaction_id' => $transaction->id,
            'branch_id' => $transaction->branch_id,
            'status' => 'capturing',
        ]);

        return response()->json([
            'session_id' => $session->id,
            'photo_count' => $transaction->package->photo_count,
        ]);
    }

    public function capturePhoto(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => ['required', 'exists:photo_sessions,id'],
            'photo_data' => ['required', 'string', 'max:20971520'], // 20 MB base64 ≈ 15 MB image
            'order' => ['required', 'integer', 'min:1'],
        ]);

        $imageData = preg_replace('#^data:image/\w+;base64,#i', '', $validated['photo_data']);
        $decoded = base64_decode($imageData, true);

        if ($decoded === false) {
            return response()->json(['error' => 'Data foto tidak valid.'], 422);
        }

        if (strlen($decoded) > 15 * 1024 * 1024) {
            return response()->json(['error' => 'Ukuran foto terlalu besar (maks 15 MB).'], 422);
        }

        $imgInfo = @getimagesizefromstring($decoded);
        if ($imgInfo === false) {
            return response()->json(['error' => 'File bukan gambar yang valid.'], 422);
        }

        $filename = 'photos/'.$validated['session_id'].'_'.$validated['order'].'_'.now()->timestamp.'.jpg';
        Storage::disk('public')->put($filename, $decoded);

        $photo = Photo::where('photo_session_id', $validated['session_id'])
            ->where('order', $validated['order'])
            ->first();

        if ($photo) {
            if ($photo->original_path) {
                Storage::disk('public')->delete($photo->original_path);
            }

            $photo->update(['original_path' => $filename]);
        } else {
            $photo = Photo::create([
                'photo_session_id' => $validated['session_id'],
                'original_path' => $filename,
                'order' => $validated['order'],
            ]);
        }

        return response()->json([
            'photo_id' => $photo->id,
            'url' => Storage::url($filename),
        ]);
    }

    public function chooseTemplate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => ['required', 'exists:photo_sessions,id'],
            'template_id' => ['required', 'exists:templates,id'],
        ]);

        PhotoSession::findOrFail($validated['session_id'])
            ->update(['template_id' => $validated['template_id']]);

        return response()->json(['success' => true]);
    }

    public function completeSession(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => ['required', 'exists:photo_sessions,id'],
            'final_image_data' => ['nullable', 'string', 'max:52428800'], // 50 MB base64 ≈ 37 MB image (covers A3 JPEG)
            'customer_email' => ['nullable', 'email'],
        ]);

        $session = PhotoSession::findOrFail($validated['session_id']);

        $updateData = [
            'status' => 'completed',
            'completed_at' => now(),
            'customer_email' => $validated['customer_email'] ?? null,
        ];

        if (! empty($validated['final_image_data'])) {
            $imageData = preg_replace('#^data:image/\w+;base64,#i', '', $validated['final_image_data']);
            $decoded = base64_decode($imageData, true);

            if ($decoded === false || strlen($decoded) > 37 * 1024 * 1024) {
                return response()->json(['error' => 'Data gambar final tidak valid atau terlalu besar (maks 37 MB).'], 422);
            }

            if (@getimagesizefromstring($decoded) === false) {
                return response()->json(['error' => 'File bukan gambar yang valid.'], 422);
            }

            $filename = 'final/'.$session->id.'_'.now()->timestamp.'.jpg';
            Storage::disk('public')->put($filename, $decoded);
            $updateData['final_image_path'] = $filename;
        }

        $session->update($updateData);
        $session->refresh();

        $finalImageUrl = $session->final_image_path ? url(Storage::url($session->final_image_path)) : null;

        if ($session->customer_email && $finalImageUrl) {
            $this->dispatchSessionCompleteEmail($session, $finalImageUrl);
        }

        return response()->json([
            'success' => true,
            'final_image_url' => $finalImageUrl,
            'download_qr_svg' => $finalImageUrl ? $this->generateQrSvgDataUri($finalImageUrl) : null,
        ]);
    }

    private function dispatchSessionCompleteEmail(PhotoSession $session, string $finalImageUrl): void
    {
        $mail = app(MailketingService::class);

        if (! $mail->isEnabled() || ! $mail->notifySessionCompleteEnabled()) {
            return;
        }

        $siteName = (string) Setting::get('site_name', 'Philo Photobooth');
        $branchName = optional($session->branch)->name ?? '-';
        $completedAt = optional($session->completed_at)->format('d F Y, H:i') ?? now()->format('d F Y, H:i');
        $subject = 'Hasil Foto Anda Siap Diunduh — '.$siteName;

        $html = view('emails.session-complete', [
            'subject' => $subject,
            'downloadUrl' => $finalImageUrl,
            'branchName' => $branchName,
            'completedAt' => $completedAt,
            'sessionId' => $session->id,
        ])->render();

        $mail->send(
            to: $session->customer_email,
            subject: $subject,
            content: $html,
        );
    }

    private function generateQrSvgDataUri(string $content): string
    {
        $svg = (new Writer(
            new ImageRenderer(
                new RendererStyle(280, 0, null, null, Fill::uniformColor(
                    new Rgb(255, 255, 255),
                    new Rgb(24, 24, 27),
                )),
                new SvgImageBackEnd
            )
        ))->writeString($content);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    private function activeProvider(): string
    {
        $provider = (string) Setting::get('payment_provider', 'doku');

        return in_array($provider, ['doku', 'duitku', 'manual'], true) ? $provider : 'doku';
    }

    private function resolveProviderForTransaction(Transaction $transaction): string
    {
        if (in_array($transaction->payment_method, ['doku', 'duitku'], true)) {
            return $transaction->payment_method;
        }

        return $this->activeProvider();
    }

    private function detectCallbackProvider(Request $request): string
    {
        if ((string) $request->header('Client-Id', '') !== '') {
            return 'doku';
        }

        if ((string) $request->input('merchantOrderId', '') !== '') {
            return 'duitku';
        }

        return $this->activeProvider();
    }
}
