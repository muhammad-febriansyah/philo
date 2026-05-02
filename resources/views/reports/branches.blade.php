@extends('layouts.admin')

@section('title', 'Laporan per Cabang')
@section('page-title', 'Performa Cabang')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Performa Cabang</li>
@endsection

@php
    $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];
    $periodLabel = $monthNames[$month] . ' ' . $year;

    $growthBadge = function ($growth) {
        if ($growth === null) {
            return '<span class="kpi-growth kpi-growth-muted"><i class="mdi mdi-minus"></i> —</span>';
        }
        if ($growth == 0) {
            return '<span class="kpi-growth kpi-growth-muted"><i class="mdi mdi-equal"></i> 0%</span>';
        }
        $isUp = $growth > 0;
        $cls = $isUp ? 'kpi-growth-up' : 'kpi-growth-down';
        $icon = $isUp ? 'mdi-arrow-up-bold' : 'mdi-arrow-down-bold';
        return '<span class="kpi-growth '.$cls.'"><i class="mdi '.$icon.'"></i> '.number_format(abs($growth), 1, ',', '.').'%</span>';
    };

    $podium = [
        0 => ['rank' => 1, 'medal' => '🥇', 'label' => 'Juara 1', 'cls' => 'podium-gold'],
        1 => ['rank' => 2, 'medal' => '🥈', 'label' => 'Juara 2', 'cls' => 'podium-silver'],
        2 => ['rank' => 3, 'medal' => '🥉', 'label' => 'Juara 3', 'cls' => 'podium-bronze'],
    ];
@endphp

@section('content')
    {{-- Filter periode --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('reports.branches') }}" class="d-flex flex-wrap align-items-center gap-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted small fw-semibold text-uppercase">Periode</span>
                            <span class="fw-bold text-dark">{{ $periodLabel }}</span>
                        </div>
                        <div class="vr d-none d-md-block"></div>
                        <div class="d-flex flex-wrap gap-2 align-items-end ms-auto">
                            <div>
                                <label for="month" class="form-label small mb-1">Bulan</label>
                                <select name="month" id="month" class="form-select form-select-sm" style="min-width: 130px;">
                                    @foreach ($monthNames as $num => $label)
                                        <option value="{{ $num }}" @selected($month === $num)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="year" class="form-label small mb-1">Tahun</label>
                                <select name="year" id="year" class="form-select form-select-sm" style="min-width: 100px;">
                                    @for ($y = now()->year; $y >= 2024; $y--)
                                        <option value="{{ $y }}" @selected($year === $y)>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="mdi mdi-filter-outline me-1"></i> Terapkan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Top 3 Podium --}}
    @if ($topBranches->where('period_revenue', '>', 0)->count() > 0)
        <div class="row g-3 mb-4">
            @foreach ($topBranches as $idx => $branch)
                @php $info = $podium[$idx]; @endphp
                <div class="col-md-4">
                    <div class="podium-card {{ $info['cls'] }}">
                        <div class="podium-medal">{{ $info['medal'] }}</div>
                        <div class="podium-body">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="podium-rank">{{ $info['label'] }}</span>
                                @if ($branch->is_active)
                                    <span class="status-pill status-pill-active"><span class="status-dot"></span> Aktif</span>
                                @else
                                    <span class="status-pill status-pill-inactive">Nonaktif</span>
                                @endif
                            </div>
                            <h5 class="podium-name mb-1">{{ $branch->name }}</h5>
                            <p class="podium-revenue mb-1">Rp {{ number_format($branch->period_revenue, 0, ',', '.') }}</p>
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <span class="podium-meta">{{ $branch->period_transactions }} transaksi</span>
                                <span class="podium-meta">·</span>
                                <span class="podium-meta">{{ $branch->market_share }}% share</span>
                            </div>
                            <div class="mt-2">
                                {!! $growthBadge($branch->growth) !!}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-light border d-flex align-items-center gap-2 mb-4">
            <i class="mdi mdi-information-outline text-muted"></i>
            <span class="text-muted">Belum ada transaksi pada periode <strong>{{ $periodLabel }}</strong>.</span>
        </div>
    @endif

    {{-- Tabel performa cabang --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between mb-3">
                        <div>
                            <h4 class="card-title mb-1">Detail Performa Cabang</h4>
                            <p class="text-muted mb-0 small">Periode <strong>{{ $periodLabel }}</strong> · Total revenue periode: <strong>Rp {{ number_format($totalPeriodRevenue, 0, ',', '.') }}</strong></p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 nowrap w-100" id="branches-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Cabang</th>
                                    <th class="text-center" width="90">Status</th>
                                    <th class="text-end">Revenue Periode</th>
                                    <th width="170">Growth</th>
                                    <th class="text-center" width="80">Tx</th>
                                    <th class="text-end" width="120">Avg/Tx</th>
                                    <th class="text-end" width="100">Share</th>
                                    <th width="140">Last Sale</th>
                                    <th class="text-end" width="140">Akumulatif</th>
                                    <th class="text-center" width="90">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($branches as $branch)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if ($branch->photo)
                                                    <img src="{{ Storage::url($branch->photo) }}" class="rounded-circle me-2" width="36" height="36" alt="{{ $branch->name }}">
                                                @else
                                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px;">
                                                        <i class="mdi mdi-store font-size-16 text-muted"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="fw-semibold">{{ $branch->name }}</div>
                                                    <small class="text-muted"><code class="text-muted">{{ $branch->code }}</code></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if ($branch->is_active)
                                                <span class="status-pill status-pill-active"><span class="status-dot"></span> Aktif</span>
                                            @else
                                                <span class="status-pill status-pill-inactive">Nonaktif</span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold">
                                            Rp {{ number_format($branch->period_revenue, 0, ',', '.') }}
                                        </td>
                                        <td>{!! $growthBadge($branch->growth) !!}</td>
                                        <td class="text-center">
                                            <span class="badge bg-soft-info text-info">{{ number_format($branch->period_transactions) }}</span>
                                        </td>
                                        <td class="text-end text-muted">
                                            @if ($branch->period_avg > 0)
                                                Rp {{ number_format($branch->period_avg, 0, ',', '.') }}
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex align-items-center justify-content-end gap-2">
                                                <div class="share-bar">
                                                    <div class="share-bar-fill" style="width: {{ $branch->market_share }}%"></div>
                                                </div>
                                                <span class="small fw-semibold text-muted">{{ $branch->market_share }}%</span>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($branch->last_sale_at)
                                                <span class="small">{{ \Carbon\Carbon::parse($branch->last_sale_at)->diffForHumans() }}</span>
                                            @else
                                                <span class="text-muted small">Belum ada</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <span class="text-muted small">Rp {{ number_format($branch->total_revenue, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('reports.revenue', ['branch_id' => $branch->id]) }}" class="btn btn-sm btn-light waves-effect" title="Lihat detail revenue cabang">
                                                <i class="mdi mdi-chart-line"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">Belum ada data cabang.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="2">TOTAL</th>
                                    <th class="text-end">Rp {{ number_format($branches->sum('period_revenue'), 0, ',', '.') }}</th>
                                    <th></th>
                                    <th class="text-center">{{ number_format($branches->sum('period_transactions')) }}</th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th class="text-end">Rp {{ number_format($branches->sum('total_revenue'), 0, ',', '.') }}</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Visualisasi --}}
    <div class="row mt-4">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-body">
                    <h4 class="card-title mb-3">Revenue Periode per Cabang</h4>
                    <div style="height: 300px;">
                        <canvas id="branchChart"></canvas>
                    </div>
                    @if ($branches->sum('period_revenue') === 0)
                        <div class="text-center text-muted mt-3">Belum ada revenue pada periode ini.</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h4 class="card-title mb-3">Market Share</h4>
                    <div style="height: 240px;">
                        <canvas id="shareChart"></canvas>
                    </div>
                    @if ($branches->sum('period_revenue') === 0)
                        <div class="text-center text-muted mt-3 small">Belum ada data.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .bg-soft-info {
        background-color: rgba(80, 165, 241, 0.12);
    }

    /* Podium */
    .podium-card {
        position: relative;
        background: #fff;
        border-radius: 16px;
        padding: 18px 20px;
        border: 1px solid #eef0f3;
        overflow: hidden;
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .podium-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
    }

    .podium-card::before {
        content: '';
        position: absolute;
        inset: 0 0 auto 0;
        height: 4px;
        background: var(--podium-color, #6c757d);
    }

    .podium-gold { --podium-color: linear-gradient(90deg, #f6c63e, #f0a800); --podium-medal-bg: #fff8d8; }
    .podium-silver { --podium-color: linear-gradient(90deg, #cfd2d8, #9ea4b0); --podium-medal-bg: #f1f2f5; }
    .podium-bronze { --podium-color: linear-gradient(90deg, #d99a6c, #b06a3a); --podium-medal-bg: #fbe4d3; }

    .podium-medal {
        position: absolute;
        top: 14px;
        right: 14px;
        font-size: 30px;
        line-height: 1;
        background: var(--podium-medal-bg);
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .podium-rank {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #6c7589;
    }

    .podium-name {
        font-size: 17px;
        font-weight: 700;
        color: #1f2a44;
        padding-right: 60px;
    }

    .podium-revenue {
        font-size: 22px;
        font-weight: 800;
        color: #1f2a44;
        margin: 4px 0;
    }

    .podium-meta {
        font-size: 12px;
        color: #6c7589;
    }

    /* Status pill */
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        font-weight: 600;
        padding: 3px 9px;
        border-radius: 999px;
        line-height: 1.4;
    }

    .status-pill-active {
        background: rgba(52,195,143,.14);
        color: #1a9f6e;
    }

    .status-pill-inactive {
        background: rgba(108,117,137,.10);
        color: #6c7589;
    }

    .status-dot {
        display: inline-block;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #34c38f;
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: .6; transform: scale(1.3); }
    }

    /* Growth badge */
    .kpi-growth {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        font-size: 11.5px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 999px;
        line-height: 1;
        white-space: nowrap;
    }

    .kpi-growth i { font-size: 13px; line-height: 1; }
    .kpi-growth-up { background: rgba(52,195,143,.14); color: #1a9f6e; }
    .kpi-growth-down { background: rgba(244,106,106,.14); color: #d04444; }
    .kpi-growth-muted { background: rgba(108,117,137,.10); color: #6c7589; }

    /* Share bar */
    .share-bar {
        position: relative;
        flex: 1;
        max-width: 60px;
        height: 6px;
        background: #eef0f3;
        border-radius: 999px;
        overflow: hidden;
    }

    .share-bar-fill {
        position: absolute;
        inset: 0 auto 0 0;
        background: linear-gradient(90deg, #50a5f1, #556ee6);
        border-radius: 999px;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/libs/chart.js/Chart.bundle.min.js') }}"></script>
<script>
    $(function () {
        var branchNames = @json($branches->pluck('name'));
        var periodRevenues = @json($branches->pluck('period_revenue'));
        var marketShares = @json($branches->pluck('market_share'));

        $('#branches-table').DataTable({
            ordering: true,
            order: [[2, 'desc']],
            responsive: true,
            pageLength: 10,
            columnDefs: [
                { orderable: false, targets: [3, 6, 7, 9] },
            ],
            language: {
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data',
                infoFiltered: '(difilter dari _MAX_ total data)',
                zeroRecords: 'Data tidak ditemukan',
                paginate: { first: 'Pertama', last: 'Terakhir', next: '&raquo;', previous: '&laquo;' },
            },
        });

        var revCtx = document.getElementById('branchChart');
        if (revCtx) {
            new Chart(revCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: branchNames,
                    datasets: [{
                        label: 'Revenue Periode',
                        data: periodRevenues,
                        backgroundColor: 'rgba(85, 110, 230, 0.5)',
                        borderColor: 'rgba(85, 110, 230, 1)',
                        borderWidth: 1
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
                                    return 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                                }
                            }
                        }]
                    },
                    tooltips: {
                        callbacks: {
                            label: function (item) {
                                return 'Revenue: Rp ' + item.yLabel.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                            }
                        }
                    }
                }
            });
        }

        var shareCtx = document.getElementById('shareChart');
        if (shareCtx) {
            new Chart(shareCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: branchNames,
                    datasets: [{
                        data: marketShares,
                        backgroundColor: [
                            '#556ee6', '#34c38f', '#50a5f1', '#f1b44c', '#8a5cf0',
                            '#f46a6a', '#74788d', '#5dd6c2', '#e3a76f', '#a07be0',
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutoutPercentage: 65,
                    legend: {
                        position: 'bottom',
                        labels: { fontSize: 11, padding: 8, boxWidth: 10 }
                    },
                    tooltips: {
                        callbacks: {
                            label: function (item, data) {
                                var label = data.labels[item.index];
                                var value = data.datasets[0].data[item.index];
                                return label + ': ' + value + '%';
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
