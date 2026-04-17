<?php

namespace App\Http\Controllers\Admin;

use App\Exports\TransactionExport;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Setting;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Yajra\DataTables\Facades\DataTables;

class TransactionController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $branches = Branch::query()
            ->where('is_active', true)
            ->when($user->isCabang(), fn ($query) => $query->whereKey($user->branch_id))
            ->get();

        return view('transactions.index', [
            'branches' => $branches,
            'isCabangUser' => $user->isCabang(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $user = auth()->user();
        $forcedBranchId = $user->isCabang() ? $user->branch_id : null;

        $query = Transaction::with(['branch', 'package'])
            ->when($forcedBranchId, fn ($q) => $q->where('branch_id', $forcedBranchId));

        if (! $forcedBranchId && $request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('branch_name', fn (Transaction $transaction) => $transaction->branch->name ?? '-')
            ->addColumn('package_name', fn (Transaction $transaction) => $transaction->package->name ?? '-')
            ->addColumn('amount_formatted', fn (Transaction $transaction) => 'Rp '.number_format($transaction->amount, 0, ',', '.'))
            ->addColumn('status_badge', function (Transaction $transaction) {
                $badges = [
                    'pending' => 'bg-warning',
                    'paid' => 'bg-success',
                    'expired' => 'bg-danger',
                    'failed' => 'bg-dark',
                    'cancelled' => 'bg-secondary',
                ];
                $color = $badges[$transaction->status] ?? 'bg-secondary';

                return '<span class="badge '.$color.'">'.strtoupper($transaction->status).'</span>';
            })
            ->addColumn('actions', function (Transaction $transaction) {
                return '<button type="button" class="btn btn-sm btn-info waves-effect btn-detail" data-id="'.$transaction->id.'">
                            <i class="mdi mdi-eye me-1"></i> Detail
                        </button>';
            })
            ->filterColumn('branch_name', fn ($q, $keyword) => $q->whereHas('branch', fn ($b) => $b->where('name', 'like', "%{$keyword}%")))
            ->filterColumn('package_name', fn ($q, $keyword) => $q->whereHas('package', fn ($p) => $p->where('name', 'like', "%{$keyword}%")))
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    public function show(Transaction $transaction): JsonResponse
    {
        $this->ensureTransactionBelongsToCurrentBranch($transaction);

        return response()->json($transaction->load(['branch', 'package', 'photoSession']));
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $user = auth()->user();
        $branchId = $user->isCabang() ? $user->branch_id : ($request->filled('branch_id') ? (int) $request->branch_id : null);

        $filename = 'transaksi_'.now()->format('Ymd_His').'.xlsx';

        return Excel::download(
            new TransactionExport($branchId, $request->status, $request->q),
            $filename
        );
    }

    public function exportPdf(Request $request): Response
    {
        $user = auth()->user();
        $branchId = $user->isCabang() ? $user->branch_id : ($request->filled('branch_id') ? (int) $request->branch_id : null);

        $query = Transaction::with(['branch', 'package'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('q'), function ($q) use ($request) {
                $kw = $request->q;
                $q->where(function ($inner) use ($kw) {
                    $inner->where('order_id', 'like', "%{$kw}%")
                        ->orWhereHas('branch', fn ($b) => $b->where('name', 'like', "%{$kw}%"))
                        ->orWhereHas('package', fn ($p) => $p->where('name', 'like', "%{$kw}%"));
                });
            })
            ->orderByDesc('created_at');

        $transactions = $query->get();
        $settings = Setting::getMany(['site_name', 'logo_path']);
        $siteName = $settings['site_name'] ?? config('app.name');
        $logoUrl = $settings['logo_path'] ? storage_path('app/public/'.$settings['logo_path']) : null;
        $filterBranch = $branchId ? Branch::find($branchId)?->name : null;

        $pdf = Pdf::loadView('transactions.pdf', [
            'transactions' => $transactions,
            'siteName' => $siteName,
            'logoUrl' => $logoUrl,
            'filterBranch' => $filterBranch,
            'filterStatus' => $request->status,
            'search' => $request->q,
        ])->setPaper('a4', 'landscape');

        $filename = 'transaksi_'.now()->format('Ymd_His').'.pdf';

        return $pdf->download($filename);
    }

    private function ensureTransactionBelongsToCurrentBranch(Transaction $transaction): void
    {
        $user = auth()->user();

        abort_if(
            $user->isCabang() && (int) $transaction->branch_id !== (int) $user->branch_id,
            403,
            'Anda tidak memiliki akses ke transaksi cabang lain.'
        );
    }
}
