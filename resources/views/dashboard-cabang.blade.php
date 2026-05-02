@extends('layouts.admin')

@section('title', 'Dashboard Cabang')

@section('page-title', 'Dashboard Cabang')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard {{ $branch?->name ?? 'Cabang' }}</li>
@endsection

@section('content')

    {{-- ─────────── Hero: personalized greeting + branch context ─────────── --}}
    <div class="row">
        <div class="col-12">
            <div class="card border-0 overflow-hidden dashboard-hero mb-4">
                <div class="hero-glow"></div>
                <div class="card-body p-4 p-lg-5">
                    <div class="row align-items-center gy-4">
                        <div class="col-lg-7">
                            <span class="badge hero-badge rounded-pill px-3 py-2 mb-3">
                                <i class="mdi mdi-store-outline me-1"></i> {{ $branch?->code ?? 'CABANG' }}
                                <span class="hero-badge-divider"></span>
                                <span class="hero-badge-status {{ $branch?->is_active ? 'live' : 'down' }}">
                                    <span class="hero-status-dot"></span> {{ $branch?->is_active ? 'Online' : 'Offline' }}
                                </span>
                            </span>
                            <h3 class="text-white mb-2">Halo, {{ auth()->user()->name }}.</h3>
                            <p class="hero-sub mb-3">
                                Berikut kondisi <strong class="text-white">{{ $branch?->name ?? 'cabang Anda' }}</strong> hari ini.
                            </p>
                            <div class="d-flex flex-wrap align-items-center gap-3 hero-meta">
                                <span><i class="mdi mdi-map-marker-outline me-1"></i> {{ $branch?->address ?? '-' }}</span>
                                @if($branch?->phone)
                                    <span><i class="mdi mdi-phone-outline me-1"></i> {{ $branch->phone }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="hero-panel ms-lg-auto">
                                <div class="hero-panel-item">
                                    <span class="hero-panel-label">Revenue Hari Ini</span>
                                    <strong>Rp {{ number_format($todayRevenue, 0, ',', '.') }}</strong>
                                </div>
                                <div class="hero-panel-item">
                                    <span class="hero-panel-label">Sesi Hari Ini</span>
                                    <strong>{{ number_format($todaySessionsCount) }} <small class="text-white-50">/ {{ number_format($todayCompletedSessions) }} selesai</small></strong>
                                </div>
                                <div class="hero-panel-item">
                                    <span class="hero-panel-label">Pending Payment</span>
                                    <strong>{{ number_format($pendingTransactions) }}</strong>
                                </div>
                                <div class="hero-panel-item">
                                    <span class="hero-panel-label">Rata-rata Nilai Tx</span>
                                    <strong>Rp {{ number_format($avgTransactionValue, 0, ',', '.') }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ─────────── Action Items + Sesi Hari Ini (NEW) ─────────── --}}
    <div class="row g-4 mb-4">
        <div class="col-xl-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <p class="text-muted text-uppercase mb-1" style="font-size:0.7rem;letter-spacing:0.1em;font-weight:600;">Butuh Perhatian</p>
                            <h5 class="mb-0 d-flex align-items-center gap-2">
                                <i class="mdi mdi-alert-circle-outline text-danger"></i>
                                Action Items
                            </h5>
                        </div>
                        @php $totalAttention = $attentionPendingOverdue + $attentionFailedToday + $attentionStuckSessions; @endphp
                        @if($totalAttention > 0)
                            <span class="attention-count">{{ $totalAttention }}</span>
                        @else
                            <span class="attention-count attention-count-ok">✓</span>
                        @endif
                    </div>

                    <div class="attention-list">
                        <a href="{{ route('transactions.index', ['status' => 'pending']) }}" class="attention-item">
                            <div class="attention-icon" style="background:#fef3c7;color:#b45309;">
                                <i class="mdi mdi-clock-alert-outline"></i>
                            </div>
                            <div class="attention-body">
                                <div class="attention-title">Pending payment >24 jam</div>
                                <div class="attention-meta">Perlu di-follow up segera</div>
                            </div>
                            <span class="attention-num {{ $attentionPendingOverdue > 0 ? 'text-warning' : 'text-muted' }}">{{ $attentionPendingOverdue }}</span>
                        </a>

                        <a href="{{ route('transactions.index', ['status' => 'failed']) }}" class="attention-item">
                            <div class="attention-icon" style="background:#fee2e2;color:#b91c1c;">
                                <i class="mdi mdi-close-circle-outline"></i>
                            </div>
                            <div class="attention-body">
                                <div class="attention-title">Failed / expired hari ini</div>
                                <div class="attention-meta">Investigasi penyebab</div>
                            </div>
                            <span class="attention-num {{ $attentionFailedToday > 0 ? 'text-danger' : 'text-muted' }}">{{ $attentionFailedToday }}</span>
                        </a>

                        <a href="{{ route('photo-sessions.index') }}" class="attention-item">
                            <div class="attention-icon" style="background:#e0e7ff;color:#4338ca;">
                                <i class="mdi mdi-camera-timer"></i>
                            </div>
                            <div class="attention-body">
                                <div class="attention-title">Sesi stuck >2 jam</div>
                                <div class="attention-meta">Status capturing belum selesai</div>
                            </div>
                            <span class="attention-num {{ $attentionStuckSessions > 0 ? 'text-primary' : 'text-muted' }}">{{ $attentionStuckSessions }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <p class="text-muted text-uppercase mb-1" style="font-size:0.7rem;letter-spacing:0.1em;font-weight:600;">Live Activity</p>
                            <h5 class="mb-0 d-flex align-items-center gap-2">
                                <span class="live-dot"></span>
                                Sesi Hari Ini
                            </h5>
                        </div>
                        <a href="{{ route('photo-sessions.index') }}" class="btn btn-sm btn-soft-primary">Lihat Semua</a>
                    </div>

                    <div class="today-session-list">
                        @forelse($todaySessions as $session)
                            <div class="today-session-row">
                                <span class="today-session-dot {{ $session->status === 'completed' ? 'session-done' : 'session-live' }}"></span>
                                <div class="today-session-body">
                                    <div class="today-session-title">{{ $session->transaction?->order_id ?? 'Tanpa Order' }}</div>
                                    <div class="today-session-meta">
                                        <span>{{ $session->template?->name ?? 'Template?' }}</span>
                                        <span class="badge {{ $session->status === 'completed' ? 'bg-soft-success text-success' : 'bg-soft-warning text-warning' }} session-status-pill">
                                            {{ $session->status === 'completed' ? 'Selesai' : 'Capturing' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="today-session-time">{{ $session->created_at->format('H:i') }}</div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">
                                <i class="mdi mdi-camera-off-outline" style="font-size:2.4rem;opacity:0.4;"></i>
                                <p class="mb-0 mt-2 small">Belum ada sesi hari ini</p>
                                <small class="text-muted">Sesi akan muncul saat customer mulai capturing.</small>
                            </div>
                        @endforelse
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
    /* ── Dashboard hero — clean dark gradient ── */
    .dashboard-hero {
        background:
            radial-gradient(ellipse at 92% -10%, rgba(232, 201, 0, 0.18) 0%, transparent 55%),
            radial-gradient(ellipse at -5% 105%, rgba(232, 201, 0, 0.10) 0%, transparent 50%),
            linear-gradient(135deg, #0a0a0a 0%, #18181b 55%, #27272a 100%);
        border-radius: 20px !important;
        position: relative;
        overflow: hidden;
        box-shadow: 0 18px 40px -18px rgba(0, 0, 0, 0.45) !important;
    }

    .dashboard-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(to right, rgba(255,255,255,0.04) 1px, transparent 1px),
            linear-gradient(to bottom, rgba(255,255,255,0.04) 1px, transparent 1px);
        background-size: 32px 32px;
        pointer-events: none;
        z-index: 0;
        mask-image: radial-gradient(ellipse at center, #000 0%, transparent 80%);
        -webkit-mask-image: radial-gradient(ellipse at center, #000 0%, transparent 80%);
    }

    .hero-glow {
        position: absolute;
        top: -120px;
        right: -100px;
        width: 360px;
        height: 360px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(232, 201, 0, 0.22) 0%, transparent 65%);
        filter: blur(40px);
        z-index: 0;
        pointer-events: none;
    }

    .dashboard-hero .card-body {
        position: relative;
        z-index: 1;
    }

    .hero-badge {
        background: rgba(232, 201, 0, 0.12) !important;
        border: 1px solid rgba(232, 201, 0, 0.3);
        letter-spacing: 0.05em;
        font-size: 0.72rem;
        font-weight: 600;
        color: #E8C900 !important;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .hero-badge-divider {
        width: 1px;
        height: 12px;
        background: rgba(232, 201, 0, 0.4);
    }

    .hero-badge-status {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.68rem;
    }

    .hero-badge-status.live { color: #22c55e; }
    .hero-badge-status.down { color: #ef4444; }

    .hero-status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
        box-shadow: 0 0 8px currentColor;
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }

    .dashboard-hero h3 {
        font-size: 1.55rem;
        font-weight: 700;
        line-height: 1.3;
        letter-spacing: -0.02em;
        color: #fafaf5 !important;
    }

    .hero-sub {
        color: rgba(250, 250, 245, 0.7) !important;
        font-size: 0.95rem;
    }

    .hero-meta {
        font-size: 0.82rem;
        color: rgba(250, 250, 245, 0.5);
    }

    .hero-meta .mdi { color: rgba(232, 201, 0, 0.7); }

    .hero-panel {
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 16px;
        padding: 1.25rem 1.4rem;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        display: grid;
        gap: 0.2rem;
    }

    .hero-panel-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.55rem 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    }

    .hero-panel-item:last-child { border-bottom: none; }
    .hero-panel-label { color: rgba(250, 250, 245, 0.6); font-size: 0.78rem; font-weight: 500; }
    .hero-panel-item strong {
        font-size: 1rem;
        font-weight: 700;
        color: #fafaf5;
        letter-spacing: -0.01em;
        font-variant-numeric: tabular-nums;
    }

    /* ── Action Items widget ── */
    .attention-count {
        background: #fef2f2;
        color: #b91c1c;
        font-size: 0.85rem;
        font-weight: 700;
        padding: 0.3rem 0.7rem;
        border-radius: 999px;
        min-width: 36px;
        text-align: center;
    }
    .attention-count-ok { background: #f0fdf4; color: #15803d; }
    .attention-list { display: flex; flex-direction: column; gap: 0.5rem; }
    .attention-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.7rem 0.75rem;
        border-radius: 10px;
        text-decoration: none;
        background: #fafaf5;
        transition: background 0.15s ease, transform 0.15s ease;
    }
    .attention-item:hover {
        background: #f4f4ed;
        transform: translateX(2px);
    }
    .attention-icon {
        width: 36px; height: 36px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.05rem; flex-shrink: 0;
    }
    .attention-body { flex: 1; min-width: 0; }
    .attention-title {
        font-size: 0.85rem;
        font-weight: 600;
        color: #18181b;
        line-height: 1.2;
    }
    .attention-meta {
        font-size: 0.72rem;
        color: #71717a;
        margin-top: 2px;
    }
    .attention-num {
        font-size: 1.15rem;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }

    /* ── Live activity / Sesi hari ini ── */
    .live-dot {
        width: 8px; height: 8px;
        background: #22c55e;
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 0 0 3px rgba(34,197,94,0.18);
        animation: livePulse 2s ease-in-out infinite;
    }
    @keyframes livePulse {
        0%, 100% { opacity: 1; box-shadow: 0 0 0 3px rgba(34,197,94,0.18); }
        50%      { opacity: 0.6; box-shadow: 0 0 0 6px rgba(34,197,94,0.08); }
    }
    .today-session-list {
        display: flex;
        flex-direction: column;
        gap: 0.3rem;
        max-height: 280px;
        overflow-y: auto;
    }
    .today-session-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.6rem 0.5rem;
        border-bottom: 1px solid rgba(0,0,0,0.04);
        border-radius: 6px;
        transition: background 0.15s;
    }
    .today-session-row:last-child { border-bottom: none; }
    .today-session-row:hover { background: #fafaf5; }
    .today-session-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .session-done { background: #22c55e; }
    .session-live {
        background: #E8C900;
        box-shadow: 0 0 0 3px rgba(232,201,0,0.2);
        animation: livePulse 2s ease-in-out infinite;
    }
    .today-session-body { flex: 1; min-width: 0; }
    .today-session-title {
        font-size: 0.85rem;
        font-weight: 600;
        color: #18181b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .today-session-meta {
        font-size: 0.72rem;
        color: #71717a;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 2px;
    }
    .session-status-pill {
        font-size: 0.62rem !important;
        padding: 0.15rem 0.5rem !important;
        font-weight: 600 !important;
    }
    .today-session-time {
        font-size: 0.78rem;
        font-weight: 600;
        color: #71717a;
        font-variant-numeric: tabular-nums;
        flex-shrink: 0;
    }

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
