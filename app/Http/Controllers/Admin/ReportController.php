<?php

namespace App\Http\Controllers\Admin;

use App\Exports\RevenueExport;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Setting;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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
        $totalDiscount = (int) (clone $paidTransactions)->sum('discount_amount');
        $voucherTransactions = (clone $paidTransactions)->whereNotNull('voucher_id')->count();
        $thisMonthRevenue = (clone $paidTransactions)
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');
        $todayRevenue = (clone $paidTransactions)
            ->whereDate('paid_at', now()->toDateString())
            ->sum('amount');

        $lastMonth = now()->subMonth();
        $lastMonthRevenue = (clone $paidTransactions)
            ->whereMonth('paid_at', $lastMonth->month)
            ->whereYear('paid_at', $lastMonth->year)
            ->sum('amount');
        $yesterdayRevenue = (clone $paidTransactions)
            ->whereDate('paid_at', now()->subDay()->toDateString())
            ->sum('amount');

        $totalTransactions = (clone $paidTransactions)->count();
        $thisMonthTransactions = (clone $paidTransactions)
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->count();
        $lastMonthTransactions = (clone $paidTransactions)
            ->whereMonth('paid_at', $lastMonth->month)
            ->whereYear('paid_at', $lastMonth->year)
            ->count();

        $avgTransaction = $totalTransactions > 0 ? (int) round($totalRevenue / $totalTransactions) : 0;
        $thisMonthAvg = $thisMonthTransactions > 0 ? (int) round($thisMonthRevenue / $thisMonthTransactions) : 0;
        $lastMonthAvg = $lastMonthTransactions > 0 ? (int) round($lastMonthRevenue / $lastMonthTransactions) : 0;

        $growth = fn ($current, $previous) => $previous > 0
            ? round((($current - $previous) / $previous) * 100, 1)
            : null;

        $monthRevenueGrowth = $growth($thisMonthRevenue, $lastMonthRevenue);
        $todayRevenueGrowth = $growth($todayRevenue, $yesterdayRevenue);
        $monthTransactionsGrowth = $growth($thisMonthTransactions, $lastMonthTransactions);
        $monthAvgGrowth = $growth($thisMonthAvg, $lastMonthAvg);

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
            'totalDiscount',
            'voucherTransactions',
            'year',
            'lastMonthRevenue',
            'yesterdayRevenue',
            'totalTransactions',
            'thisMonthTransactions',
            'avgTransaction',
            'thisMonthAvg',
            'monthRevenueGrowth',
            'todayRevenueGrowth',
            'monthTransactionsGrowth',
            'monthAvgGrowth',
        ));
    }

    public function revenueData(Request $request)
    {
        $user = auth()->user();
        $forcedBranchId = $user->isCabang() ? $user->branch_id : null;

        $query = Transaction::with(['branch', 'package', 'voucher:id,code'])
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
            ->editColumn('amount', function ($row) {
                $amount = 'Rp '.number_format($row->amount, 0, ',', '.');
                $badges = '';

                if ($row->voucher_id) {
                    $badges .= ' <span class="badge bg-soft-warning text-warning ms-1" title="Diskon voucher '.e($row->voucher?->code ?? '').'">
                        <i class="mdi mdi-ticket-percent-outline"></i>
                    </span>';
                }

                if ((int) $row->extra_prints > 0) {
                    $badges .= ' <span class="badge bg-soft-info text-info ms-1" title="Termasuk '.((int) $row->extra_prints).' cetak tambahan">
                        +'.((int) $row->extra_prints).'
                    </span>';
                }

                return $amount.$badges;
            })
            ->addColumn('branch_name', fn ($row) => $row->branch?->name ?? '-')
            ->addColumn('package_name', fn ($row) => $row->package?->name ?? '-')
            ->rawColumns(['amount'])
            ->make(true);
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $user = auth()->user();
        $forcedBranchId = $user->isCabang() ? $user->branch_id : null;
        $branchId = $forcedBranchId ?? $request->integer('branch_id') ?: null;
        $year = $request->integer('year', now()->year) ?: null;

        $filename = 'laporan-revenue-'.now()->format('Ymd-His').'.xlsx';

        return Excel::download(
            new RevenueExport($branchId, $request->start_date, $request->end_date, $year),
            $filename
        );
    }

    public function exportPdf(Request $request): Response
    {
        $user = auth()->user();
        $forcedBranchId = $user->isCabang() ? $user->branch_id : null;
        $branchId = $forcedBranchId ?? $request->integer('branch_id') ?: null;
        $year = $request->integer('year', now()->year) ?: null;

        $query = Transaction::with(['branch', 'package', 'voucher:id,code'])
            ->where('status', 'paid')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($request->start_date, fn ($q) => $q->whereDate('paid_at', '>=', $request->start_date))
            ->when($request->end_date, fn ($q) => $q->whereDate('paid_at', '<=', $request->end_date))
            ->when($year && ! $request->start_date && ! $request->end_date, fn ($q) => $q->whereYear('paid_at', $year))
            ->orderByDesc('paid_at');

        $transactions = $query->get();
        $totalRevenue = $transactions->sum('amount');
        $totalDiscount = (int) $transactions->sum('discount_amount');
        $filterBranch = $branchId ? Branch::find($branchId)?->name : null;
        $siteName = Setting::get('site_name', config('app.name'));

        $pdf = Pdf::loadView('reports.revenue-pdf', [
            'transactions' => $transactions,
            'totalRevenue' => $totalRevenue,
            'totalDiscount' => $totalDiscount,
            'filterBranch' => $filterBranch,
            'filterStartDate' => $request->start_date,
            'filterEndDate' => $request->end_date,
            'filterYear' => $year,
            'siteName' => $siteName,
        ])->setPaper('a4', 'landscape');

        $filename = 'laporan-revenue-'.now()->format('Ymd-His').'.pdf';

        return $pdf->download($filename);
    }

    public function branches(Request $request): View
    {
        $month = (int) $request->integer('month', now()->month);
        $year = (int) $request->integer('year', now()->year);

        $period = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $previousPeriod = $period->copy()->subMonth();

        $branches = Branch::withCount([
            'transactions as transactions_count' => fn ($query) => $query->where('status', 'paid'),
        ])
            ->orderBy('name')
            ->get()
            ->map(function (Branch $branch) use ($period, $previousPeriod) {
                $branch->total_revenue = (int) Transaction::where('branch_id', $branch->id)
                    ->where('status', 'paid')
                    ->sum('amount');

                $periodQuery = Transaction::where('branch_id', $branch->id)
                    ->where('status', 'paid')
                    ->whereYear('paid_at', $period->year)
                    ->whereMonth('paid_at', $period->month);

                $branch->period_revenue = (int) (clone $periodQuery)->sum('amount');
                $branch->period_transactions = (int) (clone $periodQuery)->count();
                $branch->period_discount = (int) (clone $periodQuery)->sum('discount_amount');
                $branch->period_vouchers_used = (int) (clone $periodQuery)->whereNotNull('voucher_id')->count();
                $branch->period_avg = $branch->period_transactions > 0
                    ? (int) round($branch->period_revenue / $branch->period_transactions)
                    : 0;

                $branch->previous_revenue = (int) Transaction::where('branch_id', $branch->id)
                    ->where('status', 'paid')
                    ->whereYear('paid_at', $previousPeriod->year)
                    ->whereMonth('paid_at', $previousPeriod->month)
                    ->sum('amount');

                $branch->growth = $branch->previous_revenue > 0
                    ? round((($branch->period_revenue - $branch->previous_revenue) / $branch->previous_revenue) * 100, 1)
                    : null;

                $branch->last_sale_at = Transaction::where('branch_id', $branch->id)
                    ->where('status', 'paid')
                    ->latest('paid_at')
                    ->value('paid_at');

                return $branch;
            });

        $totalPeriodRevenue = $branches->sum('period_revenue');
        $branches = $branches->map(function (Branch $branch) use ($totalPeriodRevenue) {
            $branch->market_share = $totalPeriodRevenue > 0
                ? round(($branch->period_revenue / $totalPeriodRevenue) * 100, 1)
                : 0;

            return $branch;
        });

        $topBranches = $branches->sortByDesc('period_revenue')->values()->take(3);

        return view('reports.branches', compact(
            'branches',
            'topBranches',
            'month',
            'year',
            'period',
            'totalPeriodRevenue',
        ));
    }
}
