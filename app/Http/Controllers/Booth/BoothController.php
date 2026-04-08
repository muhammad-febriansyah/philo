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
use App\Services\DuitkuService;
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

        $packages = Package::where('is_active', true)->orderBy('price')->get();
        $templates = Template::where('is_active', true)->get();
        $settings = Setting::getMany(['site_name', 'logo_path']);

        return Inertia::render('booth/show', [
            'branch' => $branch->only('id', 'name', 'code', 'photo'),
            'packages' => $packages->map(fn (Package $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'description' => $p->description,
                'photo_count' => $p->photo_count,
                'print_size' => $p->print_size,
                'price' => $p->price,
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
            'settings' => $settings,
        ]);
    }

    public function startSession(Request $request, DuitkuService $duitku): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'package_id' => ['required', 'exists:packages,id'],
        ]);

        $package = Package::findOrFail($validated['package_id']);
        $orderId = 'PHILO-' . strtoupper(Str::random(6)) . '-' . now()->format('YmdHis');

        $transaction = Transaction::create([
            'order_id' => $orderId,
            'branch_id' => $validated['branch_id'],
            'package_id' => $validated['package_id'],
            'amount' => $package->price,
            'status' => 'pending',
            'expired_at' => now()->addMinutes(15),
        ]);

        try {
            $result = $duitku->createTransaction(
                orderId: $orderId,
                amount: $package->price,
                productDetail: 'Foto Booth - ' . $package->name,
                email: 'customer@philobooth.com',
                customerName: 'Pelanggan Philo',
                callbackUrl: route('booth.payment.callback'),
                returnUrl: url('/booth'),
                expiryMinutes: 15,
            );

            $transaction->update([
                'duitku_reference' => $result['reference'],
                'qris_string' => $result['qr_string'],
                'payment_url' => $result['payment_url'],
                'expired_at' => $result['expired_at'],
            ]);

            $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=' . urlencode($result['qr_string']);
            $expiredAt = $result['expired_at'];
        } catch (\RuntimeException $e) {
            Log::warning('Duitku fallback to simulation', ['error' => $e->getMessage()]);
            // Fallback ke mode simulasi jika Duitku belum dikonfigurasi
            $transaction->update(['qris_string' => $orderId]);
            $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=' . urlencode($orderId);
            $expiredAt = $transaction->expired_at->toISOString();
        }

        return response()->json([
            'transaction_id' => $transaction->id,
            'order_id' => $transaction->order_id,
            'amount' => $transaction->amount,
            'qr_url' => $qrUrl,
            'expired_at' => $expiredAt,
        ]);
    }

    public function checkPayment(Transaction $transaction, DuitkuService $duitku): JsonResponse
    {
        if ($transaction->isPaid()) {
            return response()->json(['status' => $transaction->status, 'paid' => true]);
        }

        if ($transaction->duitku_reference) {
            try {
                $status = $duitku->checkTransaction($transaction->order_id);

                if ($status === 'paid') {
                    $transaction->update(['status' => 'paid', 'paid_at' => now()]);
                } elseif ($status === 'cancelled') {
                    $transaction->update(['status' => 'failed']);
                }

                $transaction->refresh();
            } catch (\RuntimeException $e) {
                Log::warning('Duitku checkTransaction failed', ['error' => $e->getMessage()]);
            }
        }

        return response()->json([
            'status' => $transaction->status,
            'paid' => $transaction->isPaid(),
        ]);
    }

    public function duitkuCallback(Request $request, DuitkuService $duitku): \Illuminate\Http\Response
    {
        $amount = (string) $request->input('amount');
        $orderId = (string) $request->input('merchantOrderId');
        $resultCode = (string) $request->input('resultCode');
        $signature = (string) $request->input('signature');

        if (! $duitku->verifyCallbackSignature($amount, $orderId, $signature)) {
            Log::warning('Duitku callback signature mismatch', $request->all());
            abort(403, 'Invalid signature');
        }

        if ($resultCode === '00') {
            Transaction::where('order_id', $orderId)->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
        }

        return response()->noContent();
    }

    public function simulatePayment(Transaction $transaction): JsonResponse
    {
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

        $filename = 'photos/' . $validated['session_id'] . '_' . $validated['order'] . '_' . now()->timestamp . '.jpg';
        Storage::disk('public')->put($filename, $decoded);

        $photo = Photo::create([
            'photo_session_id' => $validated['session_id'],
            'original_path' => $filename,
            'order' => $validated['order'],
        ]);

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
                $filename = 'final/' . $session->id . '_' . now()->timestamp . '.jpg';
                Storage::disk('public')->put($filename, $decoded);
                $updateData['final_image_path'] = $filename;
            }
        }

        $session->update($updateData);

        return response()->json(['success' => true]);
    }
}
