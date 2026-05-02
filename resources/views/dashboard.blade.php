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

    {{-- ─────────── KPI Strip + Action Items + Today's Sessions ─────────── --}}
    <div class="row g-4 mb-4">
        {{-- KPI strip (4 small kpis stacked on left) --}}
        <div class="col-xl-4">
            <div class="row g-3 h-100">
                <div class="col-6">
                    <div class="kpi-card kpi-rev-today">
                        <span class="kpi-label">Revenue Hari Ini</span>
                        <h4 class="kpi-value">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</h4>
                        <span class="kpi-trend {{ $todayRevenueChange >= 0 ? 'up' : 'down' }}">
                            <i class="mdi mdi-arrow-{{ $todayRevenueChange >= 0 ? 'up' : 'down' }}-bold"></i>
                            {{ $todayRevenueChange >= 0 ? '+' : '' }}{{ number_format($todayRevenueChange, 1) }}% vs kemarin
                        </span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="kpi-card kpi-rev-month">
                        <span class="kpi-label">Revenue Bulan Ini</span>
                        <h4 class="kpi-value">Rp {{ number_format($thisMonthRevenue, 0, ',', '.') }}</h4>
                        <span class="kpi-trend {{ $monthRevenueChange >= 0 ? 'up' : 'down' }}">
                            <i class="mdi mdi-arrow-{{ $monthRevenueChange >= 0 ? 'up' : 'down' }}-bold"></i>
                            {{ $monthRevenueChange >= 0 ? '+' : '' }}{{ number_format($monthRevenueChange, 1) }}% vs bln lalu
                        </span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="kpi-card kpi-tx">
                        <span class="kpi-label">Transaksi Dibayar</span>
                        <h4 class="kpi-value">{{ number_format($paidTransactionsCount) }}</h4>
                        <div class="kpi-progress">
                            <div class="kpi-progress-bar" style="width: {{ min($paymentSuccessRate, 100) }}%; background: #22c55e;"></div>
                        </div>
                        <small class="kpi-foot">{{ number_format($paymentSuccessRate, 1) }}% dari total</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="kpi-card kpi-session">
                        <span class="kpi-label">Sesi Selesai Hari Ini</span>
                        <h4 class="kpi-value">{{ number_format($todayCompletedSessions) }}<small class="text-muted fw-normal" style="font-size:0.75rem;"> / {{ number_format($todaySessionsCount) }}</small></h4>
                        <div class="kpi-progress">
                            @php $todayPct = $todaySessionsCount > 0 ? round(($todayCompletedSessions / $todaySessionsCount) * 100, 1) : 0; @endphp
                            <div class="kpi-progress-bar" style="width: {{ min($todayPct, 100) }}%; background: #E8C900;"></div>
                        </div>
                        <small class="kpi-foot">{{ $todayPct }}% terselesaikan</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Butuh Perhatian --}}
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100 attention-card">
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

        {{-- Sesi Hari Ini --}}
        <div class="col-xl-4">
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

                    <div class="session-list">
                        @forelse($todaySessions as $session)
                            <div class="session-row">
                                <div class="session-status">
                                    <span class="session-dot {{ $session->status === 'completed' ? 'session-dot-done' : 'session-dot-live' }}"></span>
                                </div>
                                <div class="session-body">
                                    <div class="session-title">{{ $session->transaction?->order_id ?? 'Tanpa Order' }}</div>
                                    <div class="session-meta">{{ $session->branch?->name ?? '-' }} • {{ $session->template?->name ?? 'Template?' }}</div>
                                </div>
                                <div class="session-time">{{ $session->created_at->format('H:i') }}</div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">
                                <i class="mdi mdi-camera-off-outline" style="font-size:2rem;opacity:0.4;"></i>
                                <p class="mb-0 mt-2 small">Belum ada sesi hari ini</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ─────────── Branch Activity Today ─────────── --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between pb-0">
                    <div>
                        <h5 class="card-title mb-1">Aktivitas Cabang Hari Ini</h5>
                        <p class="text-muted mb-0">Snapshot real-time per cabang aktif.</p>
                    </div>
                    <a href="{{ route('reports.branches') }}" class="btn btn-sm btn-soft-primary">Detail Per Cabang</a>
                </div>
                <div class="card-body">
                    <div class="branch-grid">
                        @forelse($branchActivity as $bActivity)
                            <div class="branch-tile {{ $bActivity->active_sessions > 0 ? 'branch-tile-live' : '' }}">
                                <div class="d-flex align-items-start justify-content-between mb-3">
                                    <div>
                                        <div class="branch-name">{{ $bActivity->name }}</div>
                                        <div class="branch-code">{{ $bActivity->code }}</div>
                                    </div>
                                    <span class="branch-status {{ $bActivity->active_sessions > 0 ? 'live' : 'idle' }}">
                                        @if($bActivity->active_sessions > 0)
                                            <span class="live-dot"></span> {{ $bActivity->active_sessions }} aktif
                                        @else
                                            Idle
                                        @endif
                                    </span>
                                </div>
                                <div class="branch-stats">
                                    <div class="branch-stat">
                                        <span class="branch-stat-label">Revenue</span>
                                        <strong>Rp {{ number_format($bActivity->today_revenue, 0, ',', '.') }}</strong>
                                    </div>
                                    <div class="branch-stat">
                                        <span class="branch-stat-label">Transaksi</span>
                                        <strong>{{ number_format($bActivity->today_tx) }}</strong>
                                    </div>
                                </div>
                                @if($bActivity->last_activity_at)
                                    <div class="branch-foot">Aktivitas terakhir: {{ \Carbon\Carbon::parse($bActivity->last_activity_at)->diffForHumans() }}</div>
                                @endif
                            </div>
                        @empty
                            <div class="col-12 text-center text-muted py-4">Belum ada cabang aktif.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ─────────── Revenue Trend + Peak Hour ─────────── --}}
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
                    <h5 class="card-title mb-1">Peak Hour</h5>
                    <p class="text-muted mb-0">Distribusi transaksi 7 hari terakhir.</p>
                </div>
                <div class="card-body">
                    <div class="peak-hour-best">
                        <span class="peak-hour-label">Jam paling rame</span>
                        <strong>{{ str_pad($peakHourBest, 2, '0', STR_PAD_LEFT) }}:00 – {{ str_pad($peakHourBest + 1, 2, '0', STR_PAD_LEFT) }}:00</strong>
                    </div>
                    <div class="peak-hour-bars">
                        @foreach($peakHourData as $hr => $cnt)
                            <div class="peak-hour-col" title="{{ str_pad($hr, 2, '0', STR_PAD_LEFT) }}:00 — {{ $cnt }} tx">
                                <div class="peak-hour-bar" style="height: {{ $peakHourMax > 0 ? max(($cnt / $peakHourMax) * 100, 4) : 4 }}%; background: {{ $hr === $peakHourBest ? '#E8C900' : '#e4e4e7' }};"></div>
                                @if($hr % 4 === 0)
                                    <span class="peak-hour-tick">{{ str_pad($hr, 2, '0', STR_PAD_LEFT) }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ─────────── Top Paket + Top Template ─────────── --}}
    <div class="row g-4 mb-4">
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="card-title mb-1">Top Paket (Bulan Ini)</h5>
                    <p class="text-muted mb-0">Paket dengan transaksi & revenue tertinggi.</p>
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
                                    <span class="badge bg-soft-primary text-primary">{{ number_format($package->total_transactions) }} tx</span>
                                </div>
                                <div class="mt-3 d-flex align-items-center justify-content-between">
                                    <span class="text-muted small">Revenue</span>
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
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="card-title mb-1">Top Template (Bulan Ini)</h5>
                    <p class="text-muted mb-0">Template paling sering dipakai pelanggan.</p>
                </div>
                <div class="card-body">
                    <div class="template-grid">
                        @forelse ($topTemplates as $tpl)
                            <div class="template-tile">
                                <div class="template-thumb">
                                    @if($tpl->thumbnail_path)
                                        <img src="{{ Storage::url($tpl->thumbnail_path) }}" alt="{{ $tpl->name }}">
                                    @else
                                        <div class="template-placeholder"><i class="mdi mdi-image-frame"></i></div>
                                    @endif
                                </div>
                                <div class="template-info">
                                    <h6 class="template-name mb-0">{{ $tpl->name }}</h6>
                                    <small class="text-muted">{{ strtoupper($tpl->print_size) }}</small>
                                </div>
                                <span class="template-count">{{ number_format($tpl->usage_count) }}</span>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4 col-span-2">Belum ada penggunaan template bulan ini.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ─────────── Recent Transactions ─────────── --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
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
                                    <th class="text-end">Waktu</th>
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
                                        <td class="text-end text-muted small">{{ $transaction->created_at->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">Belum ada transaksi.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    /* ── Dashboard hero — clean dark gradient + gold accents ── */
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

    .hero-deco-circle {
        position: absolute;
        border-radius: 50%;
        pointer-events: none;
        z-index: 0;
    }

    .hero-deco-circle-1 {
        width: 360px;
        height: 360px;
        top: -120px;
        right: -100px;
        background: radial-gradient(circle, rgba(232, 201, 0, 0.22) 0%, transparent 65%);
        filter: blur(40px);
    }

    .hero-deco-circle-2 { display: none; }
    .hero-deco-circle-3 { display: none; }

    .dashboard-hero .card-body {
        position: relative;
        z-index: 1;
    }

    .hero-badge {
        background: rgba(232, 201, 0, 0.14) !important;
        border: 1px solid rgba(232, 201, 0, 0.32);
        letter-spacing: 0.08em;
        font-size: 0.7rem !important;
        font-weight: 600;
        text-transform: uppercase;
        color: #E8C900 !important;
    }

    .dashboard-hero h3 {
        font-size: 1.55rem;
        font-weight: 700;
        line-height: 1.3;
        letter-spacing: -0.02em;
        color: #fafaf5 !important;
    }

    .dashboard-hero p {
        color: rgba(250, 250, 245, 0.6) !important;
        font-size: 0.92rem;
    }

    .dashboard-hero .btn-light {
        background: #E8C900;
        border: none;
        font-weight: 600;
        color: #18181b !important;
        box-shadow: 0 4px 12px rgba(232, 201, 0, 0.25);
        padding: 0.6rem 1.1rem;
        border-radius: 10px;
        font-size: 0.85rem;
        transition: all 0.18s ease;
    }

    .dashboard-hero .btn-light:hover {
        background: #d4b800;
        color: #18181b !important;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(232, 201, 0, 0.35);
    }

    .dashboard-hero .btn-outline-light {
        border: 1px solid rgba(255, 255, 255, 0.16);
        color: rgba(255, 255, 255, 0.9) !important;
        background: rgba(255, 255, 255, 0.04);
        font-weight: 500;
        padding: 0.6rem 1.1rem;
        border-radius: 10px;
        font-size: 0.85rem;
        backdrop-filter: blur(8px);
        transition: all 0.18s ease;
    }

    .dashboard-hero .btn-outline-light:hover {
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(255, 255, 255, 0.28);
        color: #fff !important;
    }

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
        padding: 0.6rem 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    }

    .hero-panel-item:last-child {
        border-bottom: none;
    }

    .hero-panel-label {
        color: rgba(250, 250, 245, 0.6);
        font-size: 0.78rem;
        font-weight: 500;
    }

    .hero-panel-item strong {
        font-size: 1rem;
        font-weight: 700;
        color: #fafaf5;
        letter-spacing: -0.01em;
        font-variant-numeric: tabular-nums;
    }

    /* ── KPI mini cards (stacked left) ── */
    .kpi-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.05);
        border-radius: 14px;
        padding: 1.1rem 1.15rem;
        height: 100%;
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
        box-shadow: 0 2px 6px rgba(0,0,0,0.03);
        transition: box-shadow 0.18s ease, transform 0.18s ease;
    }
    .kpi-card:hover {
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.07);
        transform: translateY(-1px);
    }
    .kpi-label {
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #71717a;
        font-weight: 600;
    }
    .kpi-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: #18181b;
        margin: 0;
        letter-spacing: -0.01em;
        font-variant-numeric: tabular-nums;
        line-height: 1.1;
    }
    .kpi-trend {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.72rem;
        font-weight: 600;
        margin-top: auto;
    }
    .kpi-trend.up { color: #15803d; }
    .kpi-trend.down { color: #b91c1c; }
    .kpi-progress {
        height: 4px;
        background: #f4f4ed;
        border-radius: 99px;
        overflow: hidden;
        margin-top: auto;
    }
    .kpi-progress-bar {
        height: 100%;
        border-radius: 99px;
        transition: width 0.4s ease;
    }
    .kpi-foot {
        font-size: 0.68rem;
        color: #71717a;
        font-weight: 500;
    }

    /* ── Action items (Butuh Perhatian) ── */
    .attention-card {
        background: #fff;
    }
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
    .attention-count-ok {
        background: #f0fdf4;
        color: #15803d;
    }
    .attention-list {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
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
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
        flex-shrink: 0;
    }
    .attention-body {
        flex: 1;
        min-width: 0;
    }
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
        width: 8px;
        height: 8px;
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
    .session-list {
        display: flex;
        flex-direction: column;
        gap: 0.3rem;
        max-height: 260px;
        overflow-y: auto;
    }
    .session-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.5rem 0.25rem;
        border-bottom: 1px solid rgba(0,0,0,0.04);
    }
    .session-row:last-child { border-bottom: none; }
    .session-status { width: 12px; flex-shrink: 0; display: flex; justify-content: center; }
    .session-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }
    .session-dot-done { background: #22c55e; }
    .session-dot-live {
        background: #E8C900;
        box-shadow: 0 0 0 3px rgba(232,201,0,0.2);
    }
    .session-body { flex: 1; min-width: 0; }
    .session-title {
        font-size: 0.85rem;
        font-weight: 600;
        color: #18181b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .session-meta {
        font-size: 0.72rem;
        color: #71717a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .session-time {
        font-size: 0.78rem;
        font-weight: 600;
        color: #71717a;
        font-variant-numeric: tabular-nums;
        flex-shrink: 0;
    }

    /* ── Branch activity grid ── */
    .branch-grid {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    }
    .branch-tile {
        background: #fafaf5;
        border: 1px solid rgba(0,0,0,0.05);
        border-radius: 14px;
        padding: 1.1rem 1.15rem;
        transition: border-color 0.18s ease, transform 0.18s ease;
    }
    .branch-tile:hover {
        border-color: rgba(0,0,0,0.12);
        transform: translateY(-1px);
    }
    .branch-tile-live {
        background: linear-gradient(180deg, #fef9c3 0%, #fafaf5 60%);
        border-color: rgba(232,201,0,0.35);
    }
    .branch-name {
        font-size: 0.95rem;
        font-weight: 700;
        color: #18181b;
        letter-spacing: -0.01em;
    }
    .branch-code {
        font-size: 0.7rem;
        color: #a1a1aa;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
    .branch-status {
        font-size: 0.7rem;
        font-weight: 600;
        padding: 0.3rem 0.6rem;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }
    .branch-status.live {
        background: rgba(34,197,94,0.12);
        color: #15803d;
    }
    .branch-status.idle {
        background: #f4f4ed;
        color: #71717a;
    }
    .branch-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
        padding-top: 0.85rem;
        border-top: 1px dashed rgba(0,0,0,0.08);
    }
    .branch-stat {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .branch-stat-label {
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #71717a;
        font-weight: 600;
    }
    .branch-stat strong {
        font-size: 0.92rem;
        color: #18181b;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }
    .branch-foot {
        font-size: 0.68rem;
        color: #a1a1aa;
        margin-top: 0.6rem;
        padding-top: 0.6rem;
        border-top: 1px dashed rgba(0,0,0,0.06);
    }

    /* ── Peak Hour bars ── */
    .peak-hour-best {
        display: flex;
        flex-direction: column;
        gap: 2px;
        padding: 0.85rem 1rem;
        background: #fef9c3;
        border-radius: 10px;
        margin-bottom: 1rem;
    }
    .peak-hour-label {
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #b45309;
        font-weight: 600;
    }
    .peak-hour-best strong {
        font-size: 1.05rem;
        font-weight: 700;
        color: #18181b;
        font-variant-numeric: tabular-nums;
        letter-spacing: -0.01em;
    }
    .peak-hour-bars {
        display: grid;
        grid-template-columns: repeat(24, 1fr);
        gap: 3px;
        height: 130px;
        align-items: end;
        position: relative;
        padding-bottom: 18px;
    }
    .peak-hour-col {
        position: relative;
        height: 100%;
        display: flex;
        align-items: flex-end;
    }
    .peak-hour-bar {
        width: 100%;
        border-radius: 3px 3px 0 0;
        transition: background 0.18s ease;
        min-height: 4px;
    }
    .peak-hour-col:hover .peak-hour-bar {
        background: #c9a800 !important;
    }
    .peak-hour-tick {
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        font-size: 0.62rem;
        color: #a1a1aa;
        font-weight: 500;
        font-variant-numeric: tabular-nums;
    }

    /* ── Top template grid ── */
    .template-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }
    .template-tile {
        position: relative;
        background: #fafaf5;
        border: 1px solid rgba(0,0,0,0.05);
        border-radius: 12px;
        padding: 0.65rem;
        display: flex;
        align-items: center;
        gap: 0.7rem;
    }
    .template-thumb {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        overflow: hidden;
        flex-shrink: 0;
        background: #f4f4ed;
    }
    .template-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .template-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #a1a1aa;
        font-size: 1.4rem;
    }
    .template-info {
        flex: 1;
        min-width: 0;
    }
    .template-name {
        font-size: 0.82rem;
        font-weight: 600;
        color: #18181b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .template-count {
        font-size: 0.95rem;
        font-weight: 700;
        color: #c9a800;
        background: #fef9c3;
        padding: 0.2rem 0.55rem;
        border-radius: 8px;
        font-variant-numeric: tabular-nums;
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
                    borderColor: '#c9a800',
                    backgroundColor: 'rgba(232, 201, 0, 0.14)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 4,
                    pointHoverRadius: 5,
                    pointBackgroundColor: '#E8C900',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
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

    });
</script>
@endpush
