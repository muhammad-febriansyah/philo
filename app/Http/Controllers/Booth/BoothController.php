<?php

namespace App\Http\Controllers\Booth;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Package;
use App\Models\Photo;
use App\Models\PhotoSession;
use App\Models\Setting;
use App\Models\Template;
use App\Models\Transaction;
use App\Services\DokuService;
use App\Services\DuitkuService;
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

        $packages = Package::with(['templates:id'])
            ->where('is_active', true)
            ->orderBy('price')
            ->get();
        $templates = Template::where('is_active', true)->get();
        $settings = Setting::getMany([
            'site_name',
            'logo_path',
            'booth_countdown_seconds',
            'booth_idle_timeout_seconds',
            'payment_provider',
            'duitku_payment_method',
        ]);

        return Inertia::render('booth/show', [
            'branch' => $branch->only('id', 'name', 'code', 'photo'),
            'packages' => $packages->map(fn (Package $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'description' => $p->description,
                'photo_count' => $p->photo_count,
                'print_size' => $p->print_size,
                'price' => $p->price,
                'template_ids' => $p->templates->pluck('id')->values(),
            ]),
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
                'payment_provider' => (string) ($settings['payment_provider'] ?: 'doku'),
                'duitku_payment_method' => strtoupper((string) ($settings['duitku_payment_method'] ?: 'GQ')),
            ],
        ]);
    }

    public function startSession(Request $request, DokuService $doku, DuitkuService $duitku): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'package_id' => ['required', 'exists:packages,id'],
            'payment_method_code' => ['nullable', 'string', 'max:20'],
        ]);

        $package = Package::findOrFail($validated['package_id']);
        $orderId = 'PHILO-'.strtoupper(Str::random(6)).'-'.now()->format('YmdHis');

        $transaction = Transaction::create([
            'order_id' => $orderId,
            'branch_id' => $validated['branch_id'],
            'package_id' => $validated['package_id'],
            'amount' => $package->price,
            'payment_method' => $this->activeProvider(),
            'status' => 'pending',
            'expired_at' => now()->addMinutes(15),
        ]);

        $provider = $transaction->payment_method;

        $publicBaseUrl = $request->getSchemeAndHttpHost();
        $callbackPath = route('booth.payment.callback', [], false);
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
                    amount: $package->price,
                    productDetail: 'Foto Booth - '.$package->name,
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
                    amount: $package->price,
                    productDetail: 'Foto Booth - '.$package->name,
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
                    $transaction->update(['status' => 'paid', 'paid_at' => now()]);
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
            Transaction::where('order_id', $orderId)->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
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
            Transaction::where('order_id', $orderId)->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
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

        $transaction->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

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
            'photo_data' => ['required', 'string'],
            'order' => ['required', 'integer', 'min:1'],
        ]);

        $imageData = preg_replace('#^data:image/\w+;base64,#i', '', $validated['photo_data']);
        $decoded = base64_decode($imageData, true);

        if ($decoded === false) {
            return response()->json(['error' => 'Data foto tidak valid.'], 422);
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
            'final_image_data' => ['nullable', 'string'],
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

            if ($decoded !== false) {
                $filename = 'final/'.$session->id.'_'.now()->timestamp.'.jpg';
                Storage::disk('public')->put($filename, $decoded);
                $updateData['final_image_path'] = $filename;
            }
        }

        $session->update($updateData);
        $session->refresh();

        $finalImageUrl = $session->final_image_path ? url(Storage::url($session->final_image_path)) : null;

        return response()->json([
            'success' => true,
            'final_image_url' => $finalImageUrl,
            'download_qr_svg' => $finalImageUrl ? $this->generateQrSvgDataUri($finalImageUrl) : null,
        ]);
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

        return in_array($provider, ['doku', 'duitku'], true) ? $provider : 'doku';
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
