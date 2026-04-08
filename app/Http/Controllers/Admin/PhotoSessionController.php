<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Photo;
use App\Models\PhotoSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Yajra\DataTables\Facades\DataTables;

class PhotoSessionController extends Controller
{
    /**
     * Display a listing of the photo sessions.
     */
    public function index(): View
    {
        $user = auth()->user();

        if ($user->isCabang()) {
            $branches = collect([$user->branch]);
        } else {
            $branches = \App\Models\Branch::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);
        }

        return view('photo-sessions.index', compact('branches'));
    }

    /**
     * DataTables AJAX endpoint.
     */
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
                    'completed' => 'bg-success',
                    'failed' => 'bg-danger',
                ];
                $color = $badges[$session->status] ?? 'bg-secondary';
                return '<span class="badge ' . $color . '">' . strtoupper($session->status) . '</span>';
            })
            ->addColumn('actions', function (PhotoSession $session) {
                return '<button type="button" class="btn btn-sm btn-info waves-effect btn-view" data-id="' . $session->id . '">
                            <i class="mdi mdi-image-multiple me-1"></i> Lihat Foto
                        </button>';
            })
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    /**
     * Show session details including photos.
     */
    public function show(PhotoSession $photoSession): JsonResponse
    {
        return response()->json($photoSession->load(['transaction', 'branch', 'template', 'photos']));
    }

    /**
     * Download a specific photo.
     */
    public function downloadPhoto(Photo $photo): StreamedResponse
    {
        if (!Storage::disk('public')->exists($photo->original_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk('public')->download($photo->original_path);
    }
}
