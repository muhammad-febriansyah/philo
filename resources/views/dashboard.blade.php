@extends('layouts.admin')

@section('title', 'Dashboard')

@section('page-title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Overview operasional {{ config('app.name') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card border-0 overflow-hidden dashboard-hero mb-4">
                <div class="hero-deco-circle hero-deco-circle-1"></div>
                <div class="hero-deco-circle hero-deco-circle-2"></div>
                <div class="hero-deco-circle hero-deco-circle-3"></div>
                <div class="card-body p-4 p-lg-5">
                    <div class="row align-items-center gy-4">
                        <div class="col-lg-7">
                            <span class="badge hero-badge rounded-pill text-white px-3 py-2 mb-3"><i class="mdi mdi-view-dashboard-outline me-1"></i> Control Center</span>
                            <h3 class="text-white mb-2">Halo, {{ auth()->user()->name }}. Kondisi booth hari ini terlihat terkendali.</h3>
                            <p class="text-white text-opacity-75 mb-4">
                                Pantau revenue, status pembayaran, performa cabang, dan progres sesi foto dari satu halaman.
                            </p>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('transactions.index') }}" class="btn btn-light btn-sm">
                                    <i class="mdi mdi-credit-card-outline me-1"></i> Lihat Transaksi
                                </a>
                                <a href="{{ route('reports.revenue') }}" class="btn btn-outline-light btn-sm">
                                    <i class="mdi mdi-chart-line me-1"></i> Buka Laporan
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="hero-panel ms-lg-auto">
                                <div class="hero-panel-item">
                                    <span class="hero-panel-label">Total Revenue (All Time)</span>
                                    <strong>Rp {{ number_format($totalAllTimeRevenue, 0, ',', '.') }}</strong>
                                </div>
                                <div class="hero-panel-item">
                                    <span class="hero-panel-label">Revenue Minggu Ini</span>
                                    <strong>Rp {{ number_format($thisWeekRevenue, 0, ',', '.') }}</strong>
                                </div>
                                <div class="hero-panel-item">
                                    <span class="hero-panel-label">Rata-rata Nilai Transaksi</span>
                                    <strong>Rp {{ number_format($avgTransactionValue, 0, ',', '.') }}</strong>
                                </div>
                                <div class="hero-panel-item">
                                    <span class="hero-panel-label">Pending Payment</span>
                                    <strong>{{ number_format($pendingTransactions) }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-4">
                        <div>
                            <p class="text-muted text-uppercase font-size-11 fw-bold mb-2">Revenue Hari Ini</p>
                            <h4 class="mb-0">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</h4>
                        </div>
                        <div class="stat-icon bg-soft-primary text-primary">
                            <i class="mdi mdi-cash-multiple"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="badge {{ $todayRevenueChange >= 0 ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }}">
                            {{ $todayRevenueChange >= 0 ? '+' : '' }}{{ number_format($todayRevenueChange, 1) }}%
                        </span>
                        <small class="text-muted">vs kemarin</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-4">
                        <div>
                            <p class="text-muted text-uppercase font-size-11 fw-bold mb-2">Revenue Bulan Ini</p>
                            <h4 class="mb-0">Rp {{ number_format($thisMonthRevenue, 0, ',', '.') }}</h4>
                        </div>
                        <div class="stat-icon bg-soft-info text-info">
                            <i class="mdi mdi-chart-line"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="badge {{ $monthRevenueChange >= 0 ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }}">
                            {{ $monthRevenueChange >= 0 ? '+' : '' }}{{ number_format($monthRevenueChange, 1) }}%
                        </span>
                        <small class="text-muted">vs bulan lalu</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-4">
                        <div>
                            <p class="text-muted text-uppercase font-size-11 fw-bold mb-2">Transaksi Dibayar</p>
                            <h4 class="mb-0">{{ number_format($paidTransactionsCount) }}</h4>
                        </div>
                        <div class="stat-icon bg-soft-success text-success">
                            <i class="mdi mdi-credit-card-outline"></i>
                        </div>
                    </div>
                    <div class="progress progress-sm bg-light-subtle">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ min($paymentSuccessRate, 100) }}%"></div>
                    </div>
                    <small class="text-muted d-block mt-2">{{ number_format($paymentSuccessRate, 1) }}% dari total transaksi</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-4">
                        <div>
                            <p class="text-muted text-uppercase font-size-11 fw-bold mb-2">Sesi Foto Selesai</p>
                            <h4 class="mb-0">{{ number_format($completedSessions) }}</h4>
                        </div>
                        <div class="stat-icon bg-soft-warning text-warning">
                            <i class="mdi mdi-camera-outline"></i>
                        </div>
                    </div>
                    <div class="progress progress-sm bg-light-subtle">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ min($sessionCompletionRate, 100) }}%"></div>
                    </div>
                    <small class="text-muted d-block mt-2">{{ number_format($sessionCompletionRate, 1) }}% penyelesaian sesi</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100 mini-stat-card">
                <div class="card-body">
                    <span class="mini-stat-label">Cabang Aktif</span>
                    <div class="d-flex align-items-end justify-content-between mt-3">
                        <h3 class="mb-0">{{ number_format($activeBranches) }}</h3>
                        <i class="mdi mdi-store mini-stat-icon text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100 mini-stat-card">
                <div class="card-body">
                    <span class="mini-stat-label">Paket Aktif</span>
                    <div class="d-flex align-items-end justify-content-between mt-3">
                        <h3 class="mb-0">{{ number_format($activePackages) }}</h3>
                        <i class="mdi mdi-package-variant-closed mini-stat-icon text-success"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100 mini-stat-card">
                <div class="card-body">
                    <span class="mini-stat-label">Template Aktif</span>
                    <div class="d-flex align-items-end justify-content-between mt-3">
                        <h3 class="mb-0">{{ number_format($activeTemplates) }}</h3>
                        <i class="mdi mdi-image-frame mini-stat-icon text-info"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100 mini-stat-card">
                <div class="card-body">
                    <span class="mini-stat-label">Pengguna Sistem</span>
                    <div class="d-flex align-items-end justify-content-between mt-3">
                        <h3 class="mb-0">{{ number_format($totalUsers) }}</h3>
                        <i class="mdi mdi-account-multiple-outline mini-stat-icon text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Conversion Funnel --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-4">
                    <div class="d-flex align-items-stretch justify-content-around flex-wrap gap-3 funnel-wrap">
                        <div class="funnel-node text-center">
                            <div class="funnel-icon bg-soft-primary text-primary mx-auto">
                                <i class="mdi mdi-credit-card-outline"></i>
                            </div>
                            <p class="funnel-label mb-1 mt-2">Total Transaksi</p>
                            <h4 class="mb-0 fw-bold">{{ number_format($totalTransactions) }}</h4>
                        </div>
                        <div class="funnel-divider d-flex align-items-center">
                            <i class="mdi mdi-chevron-right mdi-24px text-muted"></i>
                        </div>
                        <div class="funnel-node text-center">
                            <div class="funnel-icon bg-soft-success text-success mx-auto">
                                <i class="mdi mdi-check-circle-outline"></i>
                            </div>
                            <p class="funnel-label mb-1 mt-2">Pembayaran Sukses</p>
                            <h4 class="mb-0 fw-bold text-success">{{ number_format($paidTransactionsCount) }}</h4>
                            <small class="text-muted">{{ number_format($paymentSuccessRate, 1) }}% dari total</small>
                        </div>
                        <div class="funnel-divider d-flex align-items-center">
                            <i class="mdi mdi-chevron-right mdi-24px text-muted"></i>
                        </div>
                        <div class="funnel-node text-center">
                            <div class="funnel-icon bg-soft-info text-info mx-auto">
                                <i class="mdi mdi-camera-outline"></i>
                            </div>
                            <p class="funnel-label mb-1 mt-2">Total Sesi Foto</p>
                            <h4 class="mb-0 fw-bold text-info">{{ number_format($totalSessions) }}</h4>
                        </div>
                        <div class="funnel-divider d-flex align-items-center">
                            <i class="mdi mdi-chevron-right mdi-24px text-muted"></i>
                        </div>
                        <div class="funnel-node text-center">
                            <div class="funnel-icon bg-soft-warning text-warning mx-auto">
                                <i class="mdi mdi-image-frame"></i>
                            </div>
                            <p class="funnel-label mb-1 mt-2">Sesi Selesai</p>
                            <h4 class="mb-0 fw-bold text-warning">{{ number_format($completedSessions) }}</h4>
                            <small class="text-muted">{{ number_format($sessionCompletionRate, 1) }}% dari sesi</small>
                        </div>
                        <div class="funnel-divider d-flex align-items-center px-2">
                            <span class="funnel-vs text-muted small px-2 py-1">vs</span>
                        </div>
                        <div class="funnel-node text-center">
                            <div class="funnel-icon bg-soft-danger text-danger mx-auto">
                                <i class="mdi mdi-close-circle-outline"></i>
                            </div>
                            <p class="funnel-label mb-1 mt-2">Gagal / Dibatalkan</p>
                            <h4 class="mb-0 fw-bold text-danger">{{ number_format($cancelledTransactions) }}</h4>
                            <small class="text-muted">{{ number_format(max(0, 100 - $paymentSuccessRate), 1) }}% dari total</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 d-flex flex-wrap gap-3 align-items-center justify-content-between pb-0">
                    <div>
                        <h5 class="card-title mb-1">Revenue 7 Hari Terakhir</h5>
                        <p class="text-muted mb-0">Membantu melihat lonjakan transaksi harian secara cepat.</p>
                    </div>
                    <a href="{{ route('reports.revenue') }}" class="btn btn-sm btn-soft-primary">Lihat Detail</a>
                </div>
                <div class="card-body">
                    <div class="chart-block">
                        <canvas id="revenueTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="card-title mb-1">Komposisi Status Pembayaran</h5>
                    <p class="text-muted mb-0">Distribusi semua transaksi berdasarkan status terakhir.</p>
                </div>
                <div class="card-body">
                    <div class="chart-block chart-block-sm mb-4">
                        <canvas id="paymentStatusChart"></canvas>
                    </div>
                    <div class="status-legend">
                        @foreach ($statusBreakdown['labels'] as $index => $label)
                            <div class="status-legend-item">
                                <span class="status-dot" style="background-color: {{ $statusBreakdown['colors'][$index] }}"></span>
                                <span class="text-muted">{{ $label }}</span>
                                <strong>{{ number_format($statusBreakdown['values'][$index]) }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Monthly Revenue + Weekly Sessions Charts --}}
    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 d-flex flex-wrap gap-3 align-items-center justify-content-between pb-0">
                    <div>
                        <h5 class="card-title mb-1">Revenue Bulanan (6 Bulan)</h5>
                        <p class="text-muted mb-0">Tren pendapatan bulanan 6 bulan terakhir.</p>
                    </div>
                    <a href="{{ route('reports.revenue') }}" class="btn btn-sm btn-soft-primary">Detail Laporan</a>
                </div>
                <div class="card-body">
                    <div class="chart-block">
                        <canvas id="monthlyRevenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="card-title mb-1">Aktivitas Sesi 7 Hari</h5>
                    <p class="text-muted mb-0">Harian: sesi selesai vs belum selesai.</p>
                </div>
                <div class="card-body">
                    <div class="chart-block">
                        <canvas id="weeklySessionsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between pb-0">
                    <div>
                        <h5 class="card-title mb-1">Performa Cabang</h5>
                        <p class="text-muted mb-0">Top cabang berdasarkan revenue transaksi sukses.</p>
                    </div>
                    <a href="{{ route('reports.branches') }}" class="btn btn-sm btn-soft-primary">Per Cabang</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Cabang</th>
                                    <th class="text-center">Paid Tx</th>
                                    <th class="text-end">Revenue</th>
                                    <th width="180">Kontribusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $highestBranchRevenue = max((int) $topBranches->max('revenue'), 1);
                                @endphp
                                @forelse ($topBranches as $branch)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $branch->name }}</div>
                                            <small class="text-muted">{{ $branch->code }}</small>
                                        </td>
                                        <td class="text-center">{{ number_format($branch->paid_transactions) }}</td>
                                        <td class="text-end fw-semibold">Rp {{ number_format($branch->revenue, 0, ',', '.') }}</td>
                                        <td>
                                            <div class="progress progress-sm bg-light-subtle">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ round(((int) $branch->revenue / $highestBranchRevenue) * 100) }}%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">Belum ada data cabang untuk ditampilkan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="card-title mb-1">Paket Terlaris</h5>
                    <p class="text-muted mb-0">Paket dengan kontribusi transaksi dan revenue tertinggi.</p>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        @forelse ($topPackages as $package)
                            <div class="package-card">
                                <div class="d-flex align-items-start justify-content-between gap-3">
                                    <div>
                                        <h6 class="mb-1">{{ $package->name }}</h6>
                                        <small class="text-muted">Ukuran cetak {{ strtoupper($package->print_size) }}</small>
                                    </div>
                                    <span class="badge bg-soft-primary text-primary">{{ number_format($package->total_transactions) }} transaksi</span>
                                </div>
                                <div class="mt-3 d-flex align-items-center justify-content-between">
                                    <span class="text-muted">Revenue</span>
                                    <strong>Rp {{ number_format($package->revenue, 0, ',', '.') }}</strong>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">Belum ada paket dengan transaksi sukses.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between pb-0">
                    <div>
                        <h5 class="card-title mb-1">Transaksi Terbaru</h5>
                        <p class="text-muted mb-0">Monitoring transaksi terakhir tanpa pindah halaman.</p>
                    </div>
                    <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-soft-primary">Semua Transaksi</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Cabang</th>
                                    <th>Paket</th>
                                    <th>Status</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $statusColors = [
                                        'paid' => 'bg-success',
                                        'pending' => 'bg-warning text-dark',
                                        'expired' => 'bg-danger',
                                        'failed' => 'bg-dark',
                                        'cancelled' => 'bg-secondary',
                                    ];
                                @endphp
                                @forelse ($recentTransactions as $transaction)
                                    <tr>
                                        <td class="fw-semibold">{{ $transaction->order_id }}</td>
                                        <td>{{ $transaction->branch?->name ?? '-' }}</td>
                                        <td>{{ $transaction->package?->name ?? '-' }}</td>
                                        <td>
                                            <span class="badge {{ $statusColors[$transaction->status] ?? 'bg-secondary' }}">{{ strtoupper($transaction->status) }}</span>
                                        </td>
                                        <td class="text-end fw-semibold">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">Belum ada transaksi.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="card-title mb-1">Aktivitas Sesi Foto</h5>
                    <p class="text-muted mb-0">Snapshot progres sesi terbaru dari semua cabang.</p>
                </div>
                <div class="card-body">
                    <div class="timeline-list">
                        @forelse ($recentSessions as $session)
                            <div class="timeline-item">
                                <div class="timeline-point {{ $session->status === 'completed' ? 'bg-success' : 'bg-warning' }}"></div>
                                <div class="timeline-content">
                                    <div class="d-flex align-items-start justify-content-between gap-3">
                                        <div>
                                            <h6 class="mb-1">{{ $session->transaction?->order_id ?? 'Tanpa Order ID' }}</h6>
                                            <p class="text-muted mb-1">{{ $session->branch?->name ?? '-' }} • {{ $session->template?->name ?? 'Template belum dipilih' }}</p>
                                            <small class="text-muted">{{ optional($session->updated_at)->format('d/m/Y H:i') }}</small>
                                        </div>
                                        <span class="badge {{ $session->status === 'completed' ? 'bg-success' : 'bg-warning text-dark' }}">{{ strtoupper($session->status) }}</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">Belum ada aktivitas sesi foto.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .dashboard-hero {
        background:
            radial-gradient(ellipse at 80% -10%, rgba(232, 180, 0, 0.45) 0%, transparent 50%),
            radial-gradient(ellipse at -5% 110%, rgba(180, 110, 0, 0.45) 0%, transparent 45%),
            linear-gradient(135deg, #1a1200 0%, #3d2a00 30%, #7a5200 65%, #9e6e00 100%);
        border-radius: 24px !important;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(100, 65, 0, 0.45), 0 4px 16px rgba(0, 0, 0, 0.2) !important;
    }

    .dashboard-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: radial-gradient(rgba(255, 255, 255, 0.1) 1px, transparent 1px);
        background-size: 28px 28px;
        pointer-events: none;
        z-index: 0;
    }

    .hero-deco-circle {
        position: absolute;
        border-radius: 50%;
        pointer-events: none;
        z-index: 0;
    }

    .hero-deco-circle-1 {
        width: 400px;
        height: 400px;
        top: -140px;
        right: -80px;
        border: 1.5px solid rgba(255, 255, 255, 0.08);
        background: rgba(255, 255, 255, 0.025);
    }

    .hero-deco-circle-2 {
        width: 240px;
        height: 240px;
        top: -90px;
        right: 30px;
        border: 1px solid rgba(255, 255, 255, 0.06);
        background: transparent;
    }

    .hero-deco-circle-3 {
        width: 300px;
        height: 300px;
        bottom: -120px;
        left: 28%;
        background: radial-gradient(circle, rgba(232, 180, 0, 0.3) 0%, transparent 70%);
        filter: blur(20px);
    }

    .dashboard-hero .card-body {
        position: relative;
        z-index: 1;
    }

    .hero-badge {
        background: rgba(255, 255, 255, 0.12) !important;
        border: 1px solid rgba(255, 255, 255, 0.22);
        letter-spacing: 0.05em;
        font-size: 0.75rem;
        font-weight: 600;
        backdrop-filter: blur(4px);
    }

    .dashboard-hero h3 {
        font-size: 1.45rem;
        font-weight: 700;
        line-height: 1.35;
        letter-spacing: -0.01em;
    }

    .dashboard-hero .btn-light {
        background: rgba(255, 255, 255, 0.95);
        border: none;
        font-weight: 600;
        color: #1d4ed8;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    .dashboard-hero .btn-light:hover {
        background: #ffffff;
        color: #1a3a7a;
    }

    .dashboard-hero .btn-outline-light {
        border-color: rgba(255, 255, 255, 0.3);
        color: rgba(255, 255, 255, 0.88);
    }

    .dashboard-hero .btn-outline-light:hover {
        background: rgba(255, 255, 255, 0.12);
        border-color: rgba(255, 255, 255, 0.5);
        color: #fff;
    }

    .hero-panel {
        background: rgba(255, 255, 255, 0.09);
        border: 1px solid rgba(255, 255, 255, 0.16);
        border-radius: 20px;
        padding: 1.5rem;
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        display: grid;
        gap: 0.9rem;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.18), inset 0 1px 0 rgba(255, 255, 255, 0.12);
    }

    .hero-panel-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: #fff;
        padding: 0.4rem 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .hero-panel-item:last-child {
        border-bottom: none;
    }

    .hero-panel-label {
        color: rgba(255, 255, 255, 0.65);
        font-size: 0.82rem;
    }

    .hero-panel-item strong {
        font-size: 0.95rem;
        font-weight: 700;
        color: #fff;
    }

    .stat-card,
    .mini-stat-card {
        border-radius: 18px;
        transition: box-shadow 0.2s ease, transform 0.2s ease;
    }

    .stat-card:hover,
    .mini-stat-card:hover {
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.1) !important;
        transform: translateY(-2px);
    }

    .stat-card .card-body,
    .mini-stat-card .card-body {
        padding: 1.5rem;
    }

    .stat-icon {
        width: 54px;
        height: 54px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .mini-stat-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #6c757d;
        font-weight: 700;
    }

    .mini-stat-icon {
        font-size: 1.8rem;
        opacity: 0.6;
    }

    .chart-block {
        position: relative;
        height: 320px;
    }

    .chart-block-sm {
        height: 250px;
    }

    .package-card {
        border-radius: 14px;
        border: 1px solid rgba(15, 23, 42, 0.07);
        padding: 1.1rem 1.25rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        transition: box-shadow 0.2s ease;
    }

    .package-card:hover {
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.08);
    }

    .status-legend {
        display: grid;
        gap: 0.75rem;
    }

    .status-legend-item {
        display: grid;
        grid-template-columns: 12px 1fr auto;
        align-items: center;
        gap: 0.75rem;
    }

    .status-dot {
        width: 12px;
        height: 12px;
        border-radius: 999px;
        display: inline-block;
    }

    .timeline-list {
        position: relative;
        display: grid;
        gap: 1rem;
    }

    .timeline-item {
        display: grid;
        grid-template-columns: 14px 1fr;
        gap: 1rem;
        padding: 0.25rem 0;
    }

    .timeline-point {
        width: 14px;
        height: 14px;
        border-radius: 999px;
        margin-top: 0.35rem;
        box-shadow: 0 0 0 6px rgba(52, 195, 143, 0.12);
    }

    .timeline-content {
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(15, 23, 42, 0.06);
    }

    .bg-soft-primary {
        background-color: rgba(85, 110, 230, 0.12);
    }

    .bg-soft-success {
        background-color: rgba(52, 195, 143, 0.12);
    }

    .bg-soft-warning {
        background-color: rgba(241, 180, 76, 0.16);
    }

    .bg-soft-info {
        background-color: rgba(80, 165, 241, 0.12);
    }

    .bg-soft-danger {
        background-color: rgba(244, 106, 106, 0.12);
    }

    .funnel-wrap {
        padding: 0.75rem 0;
        row-gap: 1.25rem;
    }

    .funnel-node {
        flex: 1;
        min-width: 130px;
        max-width: 200px;
    }

    .funnel-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
    }

    .funnel-label {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #6c757d;
        font-weight: 700;
    }

    .funnel-vs {
        border: 1px dashed rgba(108, 117, 125, 0.4);
        border-radius: 6px;
        font-size: 0.75rem;
    }

    /* Card header consistent spacing */
    .card-header.bg-transparent.border-0 {
        padding: 1.25rem 1.5rem 0.75rem;
    }

    .card .card-body {
        padding: 1.25rem 1.5rem;
    }

    .card-title {
        font-size: 1rem;
        font-weight: 600;
    }

    /* Table improvements */
    .table-hover > tbody > tr:hover > td {
        background-color: rgba(85, 110, 230, 0.04);
    }

    .progress-sm {
        height: 6px;
        border-radius: 99px;
    }

    .progress-sm .progress-bar {
        border-radius: 99px;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/libs/chart.js/Chart.bundle.min.js') }}"></script>
<script>
    $(function () {
        new Chart(document.getElementById('revenueTrendChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: @json($revenueTrend['labels']),
                datasets: [{
                    label: 'Revenue',
                    data: @json($revenueTrend['values']),
                    borderColor: '#556ee6',
                    backgroundColor: 'rgba(85, 110, 230, 0.12)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 4,
                    pointHoverRadius: 5,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    display: false,
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: function (value) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                            }
                        },
                        gridLines: {
                            color: 'rgba(108, 117, 125, 0.08)'
                        }
                    }],
                    xAxes: [{
                        gridLines: {
                            display: false,
                        }
                    }]
                },
                tooltips: {
                    callbacks: {
                        label: function (tooltipItem) {
                            return 'Revenue: Rp ' + new Intl.NumberFormat('id-ID').format(tooltipItem.yLabel);
                        }
                    }
                }
            }
        });

        new Chart(document.getElementById('paymentStatusChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: @json($statusBreakdown['labels']),
                datasets: [{
                    data: @json($statusBreakdown['values']),
                    backgroundColor: @json($statusBreakdown['colors']),
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutoutPercentage: 72,
                legend: {
                    display: false,
                }
            }
        });

        new Chart(document.getElementById('monthlyRevenueChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: @json($monthlyRevenue['labels']),
                datasets: [{
                    label: 'Revenue',
                    data: @json($monthlyRevenue['values']),
                    backgroundColor: 'rgba(85, 110, 230, 0.82)',
                    borderColor: '#556ee6',
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: function (value) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                            }
                        },
                        gridLines: { color: 'rgba(108, 117, 125, 0.08)' }
                    }],
                    xAxes: [{ gridLines: { display: false } }]
                },
                tooltips: {
                    callbacks: {
                        label: function (tooltipItem) {
                            return 'Revenue: Rp ' + new Intl.NumberFormat('id-ID').format(tooltipItem.yLabel);
                        }
                    }
                }
            }
        });

        new Chart(document.getElementById('weeklySessionsChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: @json($weeklySessions['labels']),
                datasets: [
                    {
                        label: 'Selesai',
                        data: @json($weeklySessions['completed']),
                        backgroundColor: 'rgba(52, 195, 143, 0.82)',
                    },
                    {
                        label: 'Lainnya',
                        data: @json($weeklySessions['other']),
                        backgroundColor: 'rgba(241, 180, 76, 0.72)',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 12, padding: 16 }
                },
                scales: {
                    yAxes: [{ stacked: true, ticks: { beginAtZero: true, stepSize: 1 }, gridLines: { color: 'rgba(108, 117, 125, 0.08)' } }],
                    xAxes: [{ stacked: true, gridLines: { display: false } }]
                }
            }
        });
    });
</script>
@endpush
