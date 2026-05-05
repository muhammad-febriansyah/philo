<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Photo;
use App\Models\PhotoSession;
use App\Models\Setting;
use App\Services\MailketingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Yajra\DataTables\Facades\DataTables;
use ZipArchive;

class PhotoSessionController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        if ($user->isCabang()) {
            $branches = collect([$user->branch]);
        } else {
            $branches = Branch::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);
        }

        return view('photo-sessions.index', compact('branches'));
    }

    public function data(): JsonResponse
    {
        $user = auth()->user();
        $branchId = $user->isCabang() ? $user->branch_id : null;

        $query = PhotoSession::with(['transaction', 'branch', 'template'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->latest();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('order_id', fn (PhotoSession $session) => $session->transaction->order_id ?? '-')
            ->addColumn('branch_name', fn (PhotoSession $session) => $session->branch->name ?? '-')
            ->addColumn('template_name', fn (PhotoSession $session) => $session->template->name ?? '-')
            ->addColumn('status_badge', function (PhotoSession $session) {
                $badges = [
                    'pending' => 'bg-warning',
                    'capturing' => 'bg-info',
                    'completed' => 'bg-success',
                    'failed' => 'bg-danger',
                ];
                $color = $badges[$session->status] ?? 'bg-secondary';

                return '<span class="badge '.$color.'">'.strtoupper($session->status).'</span>';
            })
            ->addColumn('actions', function (PhotoSession $session) {
                $url = route('photo-sessions.show', $session);

                return '<a href="'.$url.'" class="btn btn-sm btn-info waves-effect">'
                    .'<i class="mdi mdi-eye-outline me-1"></i> Detail</a>';
            })
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    public function show(PhotoSession $photoSession): View
    {
        $this->authorizeBranch($photoSession);

        $photoSession->load([
            'transaction.branch',
            'transaction.package',
            'transaction.voucher',
            'branch',
            'template',
            'photos',
        ]);

        $finalImageUrl = $photoSession->final_image_path
            ? asset('storage/'.$photoSession->final_image_path)
            : null;

        $finalImageBytes = $photoSession->final_image_path && Storage::disk('public')->exists($photoSession->final_image_path)
            ? Storage::disk('public')->size($photoSession->final_image_path)
            : 0;

        $totalOriginalBytes = $photoSession->photos->reduce(function ($carry, Photo $photo) {
            if (Storage::disk('public')->exists($photo->original_path)) {
                return $carry + Storage::disk('public')->size($photo->original_path);
            }

            return $carry;
        }, 0);

        $timeline = $this->buildTimeline($photoSession);

        $duration = null;
        if ($photoSession->completed_at && $photoSession->created_at) {
            $duration = $photoSession->created_at->diffInSeconds($photoSession->completed_at);
        }

        return view('photo-sessions.show', [
            'session' => $photoSession,
            'finalImageUrl' => $finalImageUrl,
            'finalImageBytes' => $finalImageBytes,
            'totalOriginalBytes' => $totalOriginalBytes,
            'timeline' => $timeline,
            'duration' => $duration,
            'mailEnabled' => app(MailketingService::class)->isEnabled(),
        ]);
    }

    public function destroy(PhotoSession $photoSession): RedirectResponse
    {
        $this->authorizeBranch($photoSession);

        if ($photoSession->final_image_path && Storage::disk('public')->exists($photoSession->final_image_path)) {
            Storage::disk('public')->delete($photoSession->final_image_path);
        }

        foreach ($photoSession->photos as $photo) {
            if (Storage::disk('public')->exists($photo->original_path)) {
                Storage::disk('public')->delete($photo->original_path);
            }
        }

        $photoSession->photos()->delete();
        $photoSession->delete();

        return redirect()
            ->route('photo-sessions.index')
            ->with('success', 'Sesi foto berhasil dihapus.');
    }

    public function resendEmail(Request $request, PhotoSession $photoSession): RedirectResponse
    {
        $this->authorizeBranch($photoSession);

        $validated = $request->validate([
            'email' => ['required', 'email:rfc'],
        ]);

        if (! $photoSession->final_image_path) {
            return back()->with('error', 'Foto akhir belum tersedia, tidak dapat mengirim email.');
        }

        $mail = app(MailketingService::class);

        if (! $mail->isEnabled()) {
            return back()->with('error', 'Layanan email Mailketing belum dikonfigurasi.');
        }

        $finalImageUrl = url(Storage::url($photoSession->final_image_path));
        $siteName = (string) Setting::get('site_name', 'Philo Photobooth');
        $branchName = optional($photoSession->branch)->name ?? '-';
        $completedAt = optional($photoSession->completed_at)->format('d F Y, H:i') ?? '-';
        $subject = 'Hasil Foto Anda Siap Diunduh — '.$siteName;

        $html = view('emails.session-complete', [
            'subject' => $subject,
            'downloadUrl' => $finalImageUrl,
            'branchName' => $branchName,
            'completedAt' => $completedAt,
            'sessionId' => $photoSession->id,
        ])->render();

        $response = $mail->send(
            to: $validated['email'],
            subject: $subject,
            content: $html,
            attachments: [$finalImageUrl],
        );

        if (! ($response['success'] ?? false)) {
            Log::warning('Resend session email failed', [
                'session_id' => $photoSession->id,
                'email' => $validated['email'],
                'response' => $response,
            ]);

            return back()->with('error', 'Email gagal dikirim: '.($response['message'] ?? 'unknown error'));
        }

        $photoSession->update(['customer_email' => $validated['email']]);

        return back()->with('success', 'Email terkirim ke '.$validated['email'].'.');
    }

    public function downloadAll(PhotoSession $photoSession): BinaryFileResponse|RedirectResponse
    {
        $this->authorizeBranch($photoSession);

        $photoSession->load('photos', 'transaction');

        if ($photoSession->photos->isEmpty() && ! $photoSession->final_image_path) {
            return back()->with('error', 'Sesi ini tidak memiliki foto untuk diunduh.');
        }

        $orderId = $photoSession->transaction->order_id ?? ('session-'.$photoSession->id);
        $tmpPath = storage_path('app/tmp');

        if (! is_dir($tmpPath)) {
            mkdir($tmpPath, 0775, true);
        }

        $zipPath = $tmpPath.DIRECTORY_SEPARATOR.$orderId.'-'.now()->format('YmdHis').'.zip';
        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'Gagal membuat arsip ZIP.');
        }

        foreach ($photoSession->photos as $photo) {
            if (Storage::disk('public')->exists($photo->original_path)) {
                $absolute = Storage::disk('public')->path($photo->original_path);
                $extension = pathinfo($photo->original_path, PATHINFO_EXTENSION) ?: 'jpg';
                $zip->addFile($absolute, sprintf('captures/%02d.%s', $photo->order, $extension));
            }
        }

        if ($photoSession->final_image_path && Storage::disk('public')->exists($photoSession->final_image_path)) {
            $absolute = Storage::disk('public')->path($photoSession->final_image_path);
            $extension = pathinfo($photoSession->final_image_path, PATHINFO_EXTENSION) ?: 'jpg';
            $zip->addFile($absolute, 'final.'.$extension);
        }

        $zip->close();

        return response()->download($zipPath, $orderId.'.zip')->deleteFileAfterSend(true);
    }

    public function downloadPhoto(Photo $photo): StreamedResponse
    {
        if (! Storage::disk('public')->exists($photo->original_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk('public')->download($photo->original_path);
    }

    private function authorizeBranch(PhotoSession $session): void
    {
        $user = auth()->user();
        if ($user->isCabang() && $session->branch_id !== $user->branch_id) {
            abort(403, 'Sesi ini bukan milik cabang Anda.');
        }
    }

    /**
     * @return array<int, array{label: string, time: ?Carbon, icon: string, color: string, description: ?string}>
     */
    private function buildTimeline(PhotoSession $session): array
    {
        $tx = $session->transaction;

        return array_values(array_filter([
            [
                'label' => 'Transaksi dibuat',
                'time' => $tx?->created_at,
                'icon' => 'mdi-cart-outline',
                'color' => '#71717a',
                'description' => $tx?->order_id ? 'Order '.$tx->order_id : null,
            ],
            [
                'label' => 'Pembayaran berhasil',
                'time' => $tx?->paid_at,
                'icon' => 'mdi-check-circle-outline',
                'color' => '#22c55e',
                'description' => $tx?->payment_method ? strtoupper((string) $tx->payment_method) : null,
            ],
            [
                'label' => 'Sesi foto dimulai',
                'time' => $session->created_at,
                'icon' => 'mdi-camera-outline',
                'color' => '#0ea5e9',
                'description' => $session->branch?->name,
            ],
            [
                'label' => 'Sesi selesai',
                'time' => $session->completed_at,
                'icon' => 'mdi-flag-checkered',
                'color' => '#E8C900',
                'description' => $session->template?->name,
            ],
        ], fn ($entry) => $entry['time'] !== null));
    }
}
