@extends('layouts.admin')

@section('title', 'Dashboard Cabang')

@section('page-title', 'Dashboard Cabang')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard {{ $branch?->name ?? 'Cabang' }}</li>
@endsection

@section('content')

    {{-- Hero Section --}}
    <div class="row">
        <div class="col-12">
            <div class="card border-0 overflow-hidden dashboard-hero mb-4">
                <div class="hero-deco-circle hero-deco-circle-1"></div>
                <div class="hero-deco-circle hero-deco-circle-2"></div>
                <div class="hero-deco-circle hero-deco-circle-3"></div>
                <div class="card-body p-4 p-lg-5">
                    <div class="row align-items-center gy-4">
                        <div class="col-lg-7">
                            <span class="badge hero-badge rounded-pill text-white px-3 py-2 mb-3">
                                <i class="mdi mdi-store me-1"></i> {{ $branch?->code ?? 'Cabang' }}
                                <span class="ms-2 opacity-60">|</span>
                                <span class="ms-2 {{ $branch?->is_active ? 'text-success-emphasis' : 'text-danger-emphasis' }}">
                                    {{ $branch?->is_active ? 'Aktif' : 'Non-Aktif' }}
                                </span>
                            </span>
                            <h3 class="text-white mb-2">Halo, {{ auth()->user()->name }}.</h3>
                            <p class="text-white text-opacity-75 mb-1">
                                <i class="mdi mdi-map-marker-outline me-1"></i> {{ $branch?->address ?? '-' }}
                            </p>
                            <p class="text-white text-opacity-60 mb-4" style="font-size: .85rem;">
                                <i class="mdi mdi-phone-outline me-1"></i> {{ $branch?->phone ?? '-' }}
                            </p>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('transactions.index') }}" class="btn btn-light btn-sm">
                                    <i class="mdi mdi-credit-card-outline me-1"></i> Transaksi
                                </a>
                                <a href="{{ route('photo-sessions.index') }}" class="btn btn-outline-light btn-sm">
                                    <i class="mdi mdi-camera-outline me-1"></i> Sesi Foto
                                </a>
                                <a href="{{ route('reports.revenue') }}" class="btn btn-outline-light btn-sm">
                                    <i class="mdi mdi-chart-line me-1"></i> Laporan Revenue
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

    {{-- Stat Cards --}}
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

    {{-- Charts Row --}}
    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 d-flex flex-wrap gap-3 align-items-center justify-content-between pb-0">
                    <div>
                        <h5 class="card-title mb-1">Revenue 7 Hari Terakhir</h5>
                        <p class="text-muted mb-0">Tren pendapatan harian cabang ini.</p>
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
                    <h5 class="card-title mb-1">Status Pembayaran</h5>
                    <p class="text-muted mb-0">Distribusi transaksi cabang ini.</p>
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

    {{-- Top Packages --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between pb-0">
                    <div>
                        <h5 class="card-title mb-1">Paket Terlaris</h5>
                        <p class="text-muted mb-0">Berdasarkan revenue transaksi sukses di cabang ini.</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @forelse ($topPackages as $index => $package)
                            <div class="col-xl-3 col-md-6">
                                <div class="package-card">
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="package-rank {{ $index === 0 ? 'rank-gold' : ($index === 1 ? 'rank-silver' : ($index === 2 ? 'rank-bronze' : 'rank-default')) }}">
                                            #{{ $index + 1 }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-semibold">{{ $package->name }}</h6>
                                            <span class="badge bg-soft-info text-info">{{ strtoupper($package->print_size) }}</span>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between text-muted font-size-12 mb-1">
                                        <span>Transaksi</span>
                                        <strong class="text-dark">{{ number_format($package->total_transactions) }}x</strong>
                                    </div>
                                    <div class="d-flex justify-content-between text-muted font-size-12">
                                        <span>Revenue</span>
                                        <strong class="text-success">Rp {{ number_format($package->revenue, 0, ',', '.') }}</strong>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center text-muted py-4">Belum ada data paket.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Transactions + Sessions --}}
    <div class="row g-4 mb-4">
        <div class="col-xl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between pb-0">
                    <div>
                        <h5 class="card-title mb-1">Transaksi Terbaru</h5>
                        <p class="text-muted mb-0">6 transaksi terakhir di cabang ini.</p>
                    </div>
                    <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-soft-primary">Semua</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Paket</th>
                                    <th>Status</th>
                                    <th class="text-end">Nominal</th>
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
                                        <td>{{ $transaction->package?->name ?? '-' }}</td>
                                        <td>
                                            <span class="badge {{ $statusColors[$transaction->status] ?? 'bg-secondary' }}">{{ strtoupper($transaction->status) }}</span>
                                        </td>
                                        <td class="text-end fw-semibold">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">Belum ada transaksi.</td>
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
                <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between pb-0">
                    <div>
                        <h5 class="card-title mb-1">Aktivitas Sesi Foto</h5>
                        <p class="text-muted mb-0">Sesi terbaru di cabang ini.</p>
                    </div>
                    <a href="{{ route('photo-sessions.index') }}" class="btn btn-sm btn-soft-primary">Semua</a>
                </div>
                <div class="card-body p-0">
                    @php
                        $sessionStatusConfig = [
                            'completed'  => ['label' => 'Selesai',    'icon' => 'mdi-check-circle',      'color' => 'text-success', 'bg' => 'bg-soft-success'],
                            'capturing'  => ['label' => 'Memotret',   'icon' => 'mdi-camera',             'color' => 'text-primary', 'bg' => 'bg-soft-primary'],
                            'processing' => ['label' => 'Memproses',  'icon' => 'mdi-cog-outline',        'color' => 'text-info',    'bg' => 'bg-soft-info'],
                            'pending'    => ['label' => 'Menunggu',   'icon' => 'mdi-clock-outline',      'color' => 'text-warning', 'bg' => 'bg-soft-warning'],
                            'cancelled'  => ['label' => 'Dibatalkan', 'icon' => 'mdi-close-circle',       'color' => 'text-danger',  'bg' => 'bg-soft-danger'],
                            'expired'    => ['label' => 'Kedaluarsa', 'icon' => 'mdi-timer-off-outline',  'color' => 'text-secondary','bg' => 'bg-light'],
                        ];
                    @endphp
                    @forelse ($recentSessions as $session)
                        @php
                            $sc = $sessionStatusConfig[$session->status] ?? ['label' => strtoupper($session->status), 'icon' => 'mdi-circle-outline', 'color' => 'text-muted', 'bg' => 'bg-light'];
                        @endphp
                        <div class="session-row d-flex align-items-center gap-3 px-4 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            {{-- Status Icon --}}
                            <div class="session-icon {{ $sc['bg'] }} {{ $sc['color'] }} flex-shrink-0">
                                <i class="mdi {{ $sc['icon'] }}"></i>
                            </div>

                            {{-- Info --}}
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="session-order-id text-truncate">
                                        {{ $session->transaction?->order_id ?? '—' }}
                                    </span>
                                    <span class="badge session-badge {{ $sc['bg'] }} {{ $sc['color'] }} flex-shrink-0">
                                        {{ $sc['label'] }}
                                    </span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-muted font-size-12">
                                        <i class="mdi mdi-image-frame me-1"></i>{{ $session->template?->name ?? 'Belum pilih template' }}
                                    </span>
                                </div>
                            </div>

                            {{-- Time --}}
                            <div class="text-muted text-end flex-shrink-0 font-size-12">
                                <div>{{ optional($session->updated_at)->format('d M') }}</div>
                                <div class="fw-semibold text-dark" style="font-size: 0.78rem;">{{ optional($session->updated_at)->format('H:i') }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5">
                            <i class="mdi mdi-camera-off-outline d-block font-size-36 mb-2 opacity-50"></i>
                            Belum ada aktivitas sesi foto.
                        </div>
                    @endforelse
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
        background-image: radial-gradient(rgba(255, 255, 255, 0.08) 1px, transparent 1px);
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

    .hero-panel-item:last-child { border-bottom: none; }
    .hero-panel-label { color: rgba(255, 255, 255, 0.65); font-size: 0.82rem; }
    .hero-panel-item strong { font-size: 0.95rem; font-weight: 700; color: #fff; }

    .stat-card { border-radius: 18px; transition: box-shadow 0.2s ease, transform 0.2s ease; }
    .stat-card:hover { box-shadow: 0 8px 24px rgba(15, 23, 42, 0.1) !important; transform: translateY(-2px); }
    .stat-card .card-body { padding: 1.5rem; }

    .stat-icon {
        width: 54px; height: 54px; border-radius: 16px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.5rem; flex-shrink: 0;
    }

    .chart-block { position: relative; height: 300px; }
    .chart-block-sm { height: 220px; }

    .status-legend { display: grid; gap: 0.75rem; }
    .status-legend-item { display: grid; grid-template-columns: 12px 1fr auto; align-items: center; gap: 0.5rem; }
    .status-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }

    .package-card {
        border-radius: 14px;
        border: 1px solid rgba(15, 23, 42, 0.07);
        padding: 1.1rem 1.25rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        transition: box-shadow 0.2s ease;
        height: 100%;
    }

    .package-card:hover { box-shadow: 0 4px 16px rgba(15, 23, 42, 0.08); }

    .package-rank {
        width: 36px; height: 36px; border-radius: 10px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 0.8rem; font-weight: 800; flex-shrink: 0;
    }

    .rank-gold { background: #fff3cd; color: #856404; }
    .rank-silver { background: #e9ecef; color: #495057; }
    .rank-bronze { background: #ffe5d0; color: #842029; }
    .rank-default { background: #f0f0f0; color: #6c757d; }

    .session-row { transition: background 0.15s ease; }
    .session-row:hover { background: #f8fafc; }

    .session-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }

    .session-order-id {
        font-size: 0.78rem;
        font-weight: 700;
        font-family: monospace;
        color: #374151;
        max-width: 160px;
        display: inline-block;
    }

    .session-badge {
        font-size: 0.68rem;
        font-weight: 600;
        padding: 0.2rem 0.55rem;
        border-radius: 20px;
    }

    .timeline-list { display: grid; gap: 1rem; }
    .timeline-item { display: flex; gap: 0.75rem; align-items: flex-start; }
    .timeline-point {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-top: 5px;
        flex-shrink: 0;
    }
    .timeline-content { flex: 1; }
    .timeline-content h6 { font-size: 0.85rem; font-weight: 600; }
    .timeline-content p { font-size: 0.78rem; }

    .dashboard-hero .btn-light {
        background: rgba(255, 255, 255, 0.95); border: none;
        font-weight: 600; color: #065f46;
    }

    .dashboard-hero .btn-outline-light {
        border-color: rgba(255, 255, 255, 0.3); color: rgba(255, 255, 255, 0.88);
    }

    .dashboard-hero .btn-outline-light:hover {
        background: rgba(255, 255, 255, 0.12); border-color: rgba(255, 255, 255, 0.5); color: #fff;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/libs/chart.js/Chart.bundle.min.js') }}"></script>
<script>
    $(function () {
        // Revenue Trend Chart
        new Chart(document.getElementById('revenueTrendChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: @json($revenueTrend['labels']),
                datasets: [{
                    label: 'Revenue (Rp)',
                    data: @json($revenueTrend['values']),
                    backgroundColor: 'rgba(16, 185, 129, 0.7)',
                    borderColor: '#10b981',
                    borderWidth: 2,
                    borderRadius: 8,
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
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(tooltipItem.yLabel);
                        }
                    }
                }
            }
        });

        // Payment Status Donut
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
                legend: { display: false },
            }
        });
    });
</script>
@endpush
