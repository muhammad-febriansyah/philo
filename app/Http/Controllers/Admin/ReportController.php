<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class ReportController extends Controller
{
    public function revenue(Request $request): View
    {
        $user = auth()->user();
        $year = (int) $request->integer('year', now()->year);
        $forcedBranchId = $user->isCabang() ? $user->branch_id : null;
        $selectedBranchId = $forcedBranchId ?? $request->integer('branch_id');

        $paidTransactions = Transaction::query()
            ->where('status', 'paid')
            ->when($selectedBranchId, fn ($query) => $query->where('branch_id', $selectedBranchId));

        $monthlyRevenue = (clone $paidTransactions)
            ->whereYear('paid_at', $year)
            ->selectRaw('MONTH(paid_at) as month, SUM(amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->all();

        $chartData = [];
        for ($i = 1; $i <= 12; $i++) {
            $chartData[] = $monthlyRevenue[$i] ?? 0;
        }

        $totalRevenue = (clone $paidTransactions)->sum('amount');
        $thisMonthRevenue = (clone $paidTransactions)
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');
        $todayRevenue = (clone $paidTransactions)
            ->whereDate('paid_at', now()->toDateString())
            ->sum('amount');
        $branches = $forcedBranchId ? collect() : Branch::query()->orderBy('name')->get(['id', 'name']);
        $selectedBranch = $selectedBranchId ? Branch::find($selectedBranchId, ['id', 'name']) : null;

        return view('reports.revenue', compact(
            'branches',
            'chartData',
            'selectedBranch',
            'selectedBranchId',
            'thisMonthRevenue',
            'todayRevenue',
            'totalRevenue',
            'year',
        ));
    }

    public function revenueData(Request $request)
    {
        $user = auth()->user();
        $forcedBranchId = $user->isCabang() ? $user->branch_id : null;

        $query = Transaction::with(['branch', 'package'])
            ->where('status', 'paid')
            ->when($forcedBranchId ?? $request->integer('branch_id'), fn ($builder, $branchId) => $builder->where('branch_id', $branchId));

        if ($request->filled('start_date')) {
            $query->whereDate('paid_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('paid_at', '<=', $request->end_date);
        }

        return DataTables::of($query)
            ->editColumn('paid_at', fn ($row) => $row->paid_at?->format('d/m/Y H:i') ?? '-')
            ->editColumn('amount', fn ($row) => 'Rp ' . number_format($row->amount, 0, ',', '.'))
            ->addColumn('branch_name', fn ($row) => $row->branch?->name ?? '-')
            ->addColumn('package_name', fn ($row) => $row->package?->name ?? '-')
            ->make(true);
    }

    public function branches(Request $request): View
    {
        $branches = Branch::withCount([
                'transactions' => fn ($query) => $query->where('status', 'paid'),
            ])
            ->get()
            ->map(function ($branch) {
                $branch->total_revenue = Transaction::where('branch_id', $branch->id)
                    ->where('status', 'paid')
                    ->sum('amount');

                return $branch;
            });

        return view('reports.branches', compact('branches'));
    }
}
