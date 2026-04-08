<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class TransactionController extends Controller
{
    public function index(): View
    {
        $branches = Branch::where('is_active', true)->get();

        return view('transactions.index', compact('branches'));
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
            ->addColumn('amount_formatted', fn (Transaction $transaction) => 'Rp ' . number_format($transaction->amount, 0, ',', '.'))
            ->addColumn('status_badge', function (Transaction $transaction) {
                $badges = [
                    'pending' => 'bg-warning',
                    'paid' => 'bg-success',
                    'expired' => 'bg-danger',
                    'failed' => 'bg-dark',
                    'cancelled' => 'bg-secondary',
                ];
                $color = $badges[$transaction->status] ?? 'bg-secondary';
                return '<span class="badge ' . $color . '">' . strtoupper($transaction->status) . '</span>';
            })
            ->addColumn('actions', function (Transaction $transaction) {
                return '<button type="button" class="btn btn-sm btn-info waves-effect btn-detail" data-id="' . $transaction->id . '">
                            <i class="mdi mdi-eye me-1"></i> Detail
                        </button>';
            })
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    public function show(Transaction $transaction): JsonResponse
    {
        return response()->json($transaction->load(['branch', 'package', 'photoSession']));
    }
}
