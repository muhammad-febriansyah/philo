<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Package;
use App\Models\PhotoSession;
use App\Models\Template;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $branchId = auth()->user()->isCabang() ? auth()->user()->branch_id : null;

        $paidTransactions = Transaction::query()->where('status', 'paid')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        $todayRevenue = (clone $paidTransactions)
            ->whereDate('paid_at', now()->toDateString())
            ->sum('amount');

        $yesterdayRevenue = (clone $paidTransactions)
            ->whereDate('paid_at', now()->copy()->subDay()->toDateString())
            ->sum('amount');

        $thisMonthRevenue = (clone $paidTransactions)
            ->whereYear('paid_at', now()->year)
            ->whereMonth('paid_at', now()->month)
            ->sum('amount');

        $lastMonthRevenue = (clone $paidTransactions)
            ->whereBetween('paid_at', [
                now()->copy()->subMonthNoOverflow()->startOfMonth(),
                now()->copy()->subMonthNoOverflow()->endOfMonth(),
            ])
            ->sum('amount');

        $totalTransactions = Transaction::query()->when($branchId, fn ($q) => $q->where('branch_id', $branchId))->count();
        $paidTransactionsCount = (clone $paidTransactions)->count();
        $pendingTransactions = Transaction::query()->where('status', 'pending')->when($branchId, fn ($q) => $q->where('branch_id', $branchId))->count();
        $completedSessions = PhotoSession::query()->where('status', 'completed')->when($branchId, fn ($q) => $q->where('branch_id', $branchId))->count();
        $activeBranches = Branch::query()->where('is_active', true)->count();
        $activePackages = Package::query()->where('is_active', true)->count();
        $activeTemplates = Template::query()->where('is_active', true)->count();
        $totalUsers = User::query()->count();
        $totalSessions = PhotoSession::query()->when($branchId, fn ($q) => $q->where('branch_id', $branchId))->count();
        $totalAllTimeRevenue = (clone $paidTransactions)->sum('amount');
        $thisWeekRevenue = (clone $paidTransactions)
            ->whereBetween('paid_at', [
                now()->copy()->startOfWeek(),
                now()->copy()->endOfWeek(),
            ])
            ->sum('amount');
        $avgTransactionValue = (int) round((clone $paidTransactions)->avg('amount') ?? 0);
        $cancelledTransactions = Transaction::query()->whereIn('status', ['expired', 'failed', 'cancelled'])->count();

        $topBranches = Branch::query()
            ->leftJoin('transactions', function ($join) {
                $join->on('branches.id', '=', 'transactions.branch_id')
                    ->where('transactions.status', '=', 'paid');
            })
            ->select('branches.id', 'branches.name', 'branches.code')
            ->selectRaw('COUNT(transactions.id) as paid_transactions')
            ->selectRaw('COALESCE(SUM(transactions.amount), 0) as revenue')
            ->groupBy('branches.id', 'branches.name', 'branches.code')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        $topPackages = Package::query()
            ->leftJoin('transactions', function ($join) {
                $join->on('packages.id', '=', 'transactions.package_id')
                    ->where('transactions.status', '=', 'paid');
            })
            ->select('packages.id', 'packages.name', 'packages.print_size')
            ->selectRaw('COUNT(transactions.id) as total_transactions')
            ->selectRaw('COALESCE(SUM(transactions.amount), 0) as revenue')
            ->groupBy('packages.id', 'packages.name', 'packages.print_size')
            ->orderByDesc('revenue')
            ->limit(4)
            ->get();

        $recentTransactions = Transaction::query()
            ->with(['branch:id,name', 'package:id,name'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->latest()
            ->limit(6)
            ->get();

        $recentSessions = PhotoSession::query()
            ->with(['branch:id,name', 'template:id,name', 'transaction:id,order_id'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', [
            'todayRevenue' => $todayRevenue,
            'todayRevenueChange' => $this->percentChange($todayRevenue, $yesterdayRevenue),
            'thisMonthRevenue' => $thisMonthRevenue,
            'monthRevenueChange' => $this->percentChange($thisMonthRevenue, $lastMonthRevenue),
            'totalTransactions' => $totalTransactions,
            'paidTransactionsCount' => $paidTransactionsCount,
            'pendingTransactions' => $pendingTransactions,
            'completedSessions' => $completedSessions,
            'activeBranches' => $activeBranches,
            'activePackages' => $activePackages,
            'activeTemplates' => $activeTemplates,
            'totalUsers' => $totalUsers,
            'paymentSuccessRate' => $this->percentage($paidTransactionsCount, $totalTransactions),
            'sessionCompletionRate' => $this->percentage($completedSessions, $totalSessions),
            'revenueTrend' => $this->dailyRevenueTrend($branchId),
            'statusBreakdown' => $this->statusBreakdown($branchId),
            'topBranches' => $topBranches,
            'topPackages' => $topPackages,
            'recentTransactions' => $recentTransactions,
            'recentSessions' => $recentSessions,
            'totalAllTimeRevenue' => $totalAllTimeRevenue,
            'thisWeekRevenue' => $thisWeekRevenue,
            'avgTransactionValue' => $avgTransactionValue,
            'cancelledTransactions' => $cancelledTransactions,
            'totalSessions' => $totalSessions,
            'monthlyRevenue' => $this->monthlyRevenueTrend($branchId),
            'weeklySessions' => $this->weeklySessionsChart($branchId),
        ]);
    }

    /**
     * @return array{labels: array<int, string>, values: array<int, int>}
     */
    private function dailyRevenueTrend(?int $branchId = null): array
    {
        $startDate = now()->copy()->subDays(6)->startOfDay();

        $groupedRevenue = Transaction::query()
            ->where('status', 'paid')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereDate('paid_at', '>=', $startDate->toDateString())
            ->selectRaw('DATE(paid_at) as paid_date, SUM(amount) as total')
            ->groupBy(DB::raw('DATE(paid_at)'))
            ->pluck('total', 'paid_date');

        $labels = [];
        $values = [];

        foreach (CarbonPeriod::create($startDate, now()->startOfDay()) as $date) {
            $dateKey = $date->format('Y-m-d');
            $labels[] = $date->format('d M');
            $values[] = (int) ($groupedRevenue[$dateKey] ?? 0);
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * @return array{labels: array<int, string>, values: array<int, int>, colors: array<int, string>}
     */
    private function statusBreakdown(?int $branchId = null): array
    {
        $statusMap = [
            'paid' => ['label' => 'Paid', 'color' => '#34c38f'],
            'pending' => ['label' => 'Pending', 'color' => '#f1b44c'],
            'expired' => ['label' => 'Expired', 'color' => '#f46a6a'],
            'failed' => ['label' => 'Failed', 'color' => '#495057'],
            'cancelled' => ['label' => 'Cancelled', 'color' => '#74788d'],
        ];

        $counts = Transaction::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->select('status')
            ->selectRaw('COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return [
            'labels' => collect($statusMap)->pluck('label')->values()->all(),
            'values' => collect($statusMap)->keys()->map(fn ($status) => (int) ($counts[$status] ?? 0))->all(),
            'colors' => collect($statusMap)->pluck('color')->values()->all(),
        ];
    }

    /**
     * @return array{labels: array<int, string>, values: array<int, int>}
     */
    private function monthlyRevenueTrend(?int $branchId = null): array
    {
        $months = collect();

        for ($i = 5; $i >= 0; $i--) {
            $months->push(now()->copy()->subMonths($i));
        }

        $grouped = Transaction::query()
            ->where('status', 'paid')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereDate('paid_at', '>=', now()->copy()->subMonths(5)->startOfMonth()->toDateString())
            ->selectRaw('YEAR(paid_at) as yr, MONTH(paid_at) as mo, SUM(amount) as total')
            ->groupBy(DB::raw('YEAR(paid_at)'), DB::raw('MONTH(paid_at)'))
            ->get()
            ->mapWithKeys(fn (object $row): array => ["{$row->yr}-{$row->mo}" => (int) $row->total]);

        return [
            'labels' => $months->map(fn ($d) => $d->format('M Y'))->all(),
            'values' => $months->map(fn ($d) => $grouped["{$d->year}-{$d->month}"] ?? 0)->all(),
        ];
    }

    /**
     * @return array{labels: array<int, string>, completed: array<int, int>, other: array<int, int>}
     */
    private function weeklySessionsChart(?int $branchId = null): array
    {
        $startDate = now()->copy()->subDays(6)->startOfDay();

        $grouped = PhotoSession::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereDate('created_at', '>=', $startDate->toDateString())
            ->selectRaw('DATE(created_at) as session_date, status, COUNT(*) as total')
            ->groupBy(DB::raw('DATE(created_at)'), 'status')
            ->get()
            ->groupBy('session_date');

        $labels = [];
        $completedValues = [];
        $otherValues = [];

        foreach (CarbonPeriod::create($startDate, now()->startOfDay()) as $date) {
            $dateKey = $date->format('Y-m-d');
            $dayData = $grouped[$dateKey] ?? collect();
            $labels[] = $date->format('d M');
            $completedValues[] = (int) $dayData->where('status', 'completed')->sum('total');
            $otherValues[] = (int) $dayData->where('status', '!=', 'completed')->sum('total');
        }

        return [
            'labels' => $labels,
            'completed' => $completedValues,
            'other' => $otherValues,
        ];
    }

    private function percentage(int $numerator, int $denominator): float
    {
        if ($denominator === 0) {
            return 0.0;
        }

        return round(($numerator / $denominator) * 100, 1);
    }

    private function percentChange(int $current, int $previous): float
    {
        if ($previous === 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
