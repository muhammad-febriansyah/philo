@extends('layouts.admin')

@section('title', 'Laporan Revenue')
@section('page-title', 'Revenue')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Revenue</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            @if ($selectedBranch)
                <div class="alert alert-info border-0 d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <strong>Filter cabang aktif:</strong> {{ $selectedBranch->name }}
                    </div>
                    <a href="{{ route('reports.revenue') }}" class="btn btn-sm btn-light">Reset Filter</a>
                </div>
            @endif
        </div>
    </div>

    @php
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
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-xl col-lg-4 col-md-6">
            <div class="kpi-card kpi-card-primary h-100">
                <div class="kpi-card-body">
                    <div class="kpi-icon"><i class="mdi mdi-cash-multiple"></i></div>
                    <p class="kpi-label">Total Revenue</p>
                    <h4 class="kpi-value">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h4>
                    <span class="kpi-growth kpi-growth-muted"><i class="mdi mdi-infinity"></i> Akumulatif</span>
                </div>
            </div>
        </div>
        <div class="col-xl col-lg-4 col-md-6">
            <div class="kpi-card kpi-card-success h-100">
                <div class="kpi-card-body">
                    <div class="kpi-icon"><i class="mdi mdi-calendar-month-outline"></i></div>
                    <p class="kpi-label">Bulan Ini</p>
                    <h4 class="kpi-value">Rp {{ number_format($thisMonthRevenue, 0, ',', '.') }}</h4>
                    {!! $growthBadge($monthRevenueGrowth) !!}
                </div>
            </div>
        </div>
        <div class="col-xl col-lg-4 col-md-6">
            <div class="kpi-card kpi-card-info h-100">
                <div class="kpi-card-body">
                    <div class="kpi-icon"><i class="mdi mdi-clock-outline"></i></div>
                    <p class="kpi-label">Hari Ini</p>
                    <h4 class="kpi-value">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</h4>
                    {!! $growthBadge($todayRevenueGrowth) !!}
                </div>
            </div>
        </div>
        <div class="col-xl col-lg-4 col-md-6">
            <div class="kpi-card kpi-card-warning h-100">
                <div class="kpi-card-body">
                    <div class="kpi-icon"><i class="mdi mdi-cart-outline"></i></div>
                    <p class="kpi-label">Total Transaksi</p>
                    <h4 class="kpi-value">{{ number_format($totalTransactions, 0, ',', '.') }} <span class="kpi-unit">tx</span></h4>
                    <span class="kpi-sub">{{ number_format($thisMonthTransactions, 0, ',', '.') }} bulan ini · {!! $growthBadge($monthTransactionsGrowth) !!}</span>
                </div>
            </div>
        </div>
        <div class="col-xl col-lg-4 col-md-6">
            <div class="kpi-card kpi-card-purple h-100">
                <div class="kpi-card-body">
                    <div class="kpi-icon"><i class="mdi mdi-chart-bell-curve"></i></div>
                    <p class="kpi-label">Rata-rata per Transaksi</p>
                    <h4 class="kpi-value">Rp {{ number_format($avgTransaction, 0, ',', '.') }}</h4>
                    <span class="kpi-sub">Bulan ini Rp {{ number_format($thisMonthAvg, 0, ',', '.') }} · {!! $growthBadge($monthAvgGrowth) !!}</span>
                </div>
            </div>
        </div>
        <div class="col-xl col-lg-4 col-md-6">
            <div class="kpi-card kpi-card-warning h-100" style="background: linear-gradient(135deg, #fef3c7 0%, #fff 100%);">
                <div class="kpi-card-body">
                    <div class="kpi-icon" style="background:rgba(217,119,6,.12);color:#92400e;"><i class="mdi mdi-ticket-percent-outline"></i></div>
                    <p class="kpi-label">Total Diskon</p>
                    <h4 class="kpi-value text-warning">Rp {{ number_format($totalDiscount, 0, ',', '.') }}</h4>
                    <span class="kpi-sub">{{ number_format($voucherTransactions, 0, ',', '.') }} transaksi pakai voucher</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-3 align-items-end justify-content-between mb-4">
                        <div>
                            <h4 class="card-title mb-1">Grafik Pendapatan Bulanan ({{ $year }})</h4>
                            <p class="text-muted mb-0">Ringkasan pendapatan transaksi berstatus dibayar.</p>
                        </div>
                        <form method="GET" action="{{ route('reports.revenue') }}" class="row g-2 align-items-end">
                            <div class="col-sm-auto">
                                <label for="branch_id" class="form-label">Cabang</label>
                                <select name="branch_id" id="branch_id" class="form-select form-select-sm">
                                    <option value="">Semua Cabang</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}" @selected($selectedBranchId === $branch->id)>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-auto">
                                <label for="year" class="form-label">Tahun</label>
                                <select name="year" id="year" class="form-select form-select-sm">
                                    @for ($y = now()->year; $y >= 2024; $y--)
                                        <option value="{{ $y }}" @selected($year === $y)>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-sm-auto">
                                <button type="submit" class="btn btn-primary btn-sm">Terapkan</button>
                            </div>
                        </form>
                    </div>
                    <div class="chart-container" style="position: relative; height: 320px;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                    @if (collect($chartData)->sum() === 0)
                        <div class="text-center text-muted mt-3">Belum ada data revenue pada filter yang dipilih.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-3 align-items-end justify-content-between mb-4">
                        <div>
                            <h4 class="card-title mb-1">Detail Transaksi</h4>
                            <p class="text-muted mb-0">Daftar transaksi sukses yang masuk ke perhitungan laporan.</p>
                        </div>
                        <div class="row g-2 align-items-end mb-0">
                            <div class="col-md-auto">
                                <label for="start_date" class="form-label">Tanggal Mulai</label>
                                <div class="input-group">
                                    <input type="text" id="start_date" class="form-control datepicker" placeholder="dd/mm/yyyy" autocomplete="off" style="max-width:140px">
                                    <span class="input-group-text"><i class="mdi mdi-calendar-outline"></i></span>
                                </div>
                            </div>
                            <div class="col-md-auto">
                                <label for="end_date" class="form-label">Tanggal Selesai</label>
                                <div class="input-group">
                                    <input type="text" id="end_date" class="form-control datepicker" placeholder="dd/mm/yyyy" autocomplete="off" style="max-width:140px">
                                    <span class="input-group-text"><i class="mdi mdi-calendar-outline"></i></span>
                                </div>
                            </div>
                            <div class="col-md-auto">
                                <button type="button" class="btn btn-primary" id="btn-filter">
                                    <i class="mdi mdi-filter-outline me-1"></i> Filter
                                </button>
                            </div>
                            <div class="col-md-auto">
                                <div class="dropdown">
                                    <button class="btn btn-success dropdown-toggle" type="button" id="btn-export" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="mdi mdi-download-outline me-1"></i> Export
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="btn-export">
                                        <li>
                                            <a class="dropdown-item" href="#" id="export-excel">
                                                <i class="mdi mdi-microsoft-excel me-2 text-success"></i> Export Excel
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" id="export-pdf">
                                                <i class="mdi mdi-file-pdf-box me-2 text-danger"></i> Export PDF
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 dt-responsive nowrap w-100" id="revenue-table">
                            <thead class="table-light">
                                <tr>
                                    <th>ID Transaksi</th>
                                    <th>Tanggal</th>
                                    <th>Cabang</th>
                                    <th>Paket</th>
                                    <th>Metode</th>
                                    <th class="text-end">Jumlah</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}">
<style>
    .bg-soft-primary {
        background-color: rgba(85, 110, 230, 0.12);
    }

    .bg-soft-success {
        background-color: rgba(52, 195, 143, 0.12);
    }

    .bg-soft-info {
        background-color: rgba(80, 165, 241, 0.12);
    }

    .kpi-card {
        background: #fff;
        border: 1px solid #eef0f3;
        border-radius: 14px;
        position: relative;
        overflow: hidden;
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(15, 23, 42, 0.06);
    }

    .kpi-card::before {
        content: '';
        position: absolute;
        inset: 0 0 auto 0;
        height: 3px;
        background: var(--kpi-color, #6c757d);
    }

    .kpi-card-primary { --kpi-color: #556ee6; --kpi-bg: rgba(85,110,230,.10); --kpi-text: #556ee6; }
    .kpi-card-success { --kpi-color: #34c38f; --kpi-bg: rgba(52,195,143,.10); --kpi-text: #1a9f6e; }
    .kpi-card-info    { --kpi-color: #50a5f1; --kpi-bg: rgba(80,165,241,.10); --kpi-text: #2f84d0; }
    .kpi-card-warning { --kpi-color: #f1b44c; --kpi-bg: rgba(241,180,76,.12); --kpi-text: #c98a1f; }
    .kpi-card-purple  { --kpi-color: #8a5cf0; --kpi-bg: rgba(138,92,240,.10); --kpi-text: #6a3ed0; }

    .kpi-card-body {
        padding: 18px 18px 16px;
    }

    .kpi-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: var(--kpi-bg);
        color: var(--kpi-text);
        font-size: 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
    }

    .kpi-label {
        margin: 0;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #6c7589;
    }

    .kpi-value {
        margin: 4px 0 8px;
        font-size: 22px;
        font-weight: 700;
        color: #1f2a44;
        line-height: 1.15;
    }

    .kpi-unit {
        font-size: 13px;
        font-weight: 600;
        color: #8590a8;
        margin-left: 2px;
    }

    .kpi-sub {
        display: block;
        font-size: 11.5px;
        color: #6c7589;
        line-height: 1.45;
    }

    .kpi-growth {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        font-size: 11.5px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 999px;
        line-height: 1;
    }

    .kpi-growth i {
        font-size: 13px;
        line-height: 1;
    }

    .kpi-growth-up {
        background: rgba(52,195,143,.14);
        color: #1a9f6e;
    }

    .kpi-growth-down {
        background: rgba(244,106,106,.14);
        color: #d04444;
    }

    .kpi-growth-muted {
        background: rgba(108,117,137,.10);
        color: #6c7589;
    }

    @media (max-width: 1199.98px) {
        .kpi-value { font-size: 20px; }
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/libs/moment/moment.js') }}"></script>
<script src="{{ asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('assets/libs/bootstrap-datepicker/locales/bootstrap-datepicker.id.min.js') }}"></script>
<script src="{{ asset('assets/libs/chart.js/Chart.bundle.min.js') }}"></script>
<script>
    $(function () {
        // Datepicker
        $('.datepicker').datepicker({
            format: 'dd/mm/yyyy',
            autoclose: true,
            todayHighlight: true,
            language: 'id',
        });

        // Datepicker end_date minimum = start_date
        $('#start_date').on('changeDate', function () {
            $('#end_date').datepicker('setStartDate', $(this).datepicker('getDate'));
        });

        var chartCanvas = document.getElementById('revenueChart');
        var chartData = @json($chartData);
        var selectedBranchId = @json($selectedBranchId);

        if (chartCanvas && typeof Chart !== 'undefined') {
            new Chart(chartCanvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                    datasets: [{
                        label: 'Revenue',
                        data: chartData,
                        backgroundColor: 'rgba(69, 137, 247, 0.2)',
                        borderColor: 'rgba(69, 137, 247, 1)',
                        borderWidth: 2,
                        pointBackgroundColor: 'rgba(69, 137, 247, 1)',
                        tension: 0.3,
                        fill: true
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
                                    return 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                                },
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
                                return 'Revenue: Rp ' + tooltipItem.yLabel.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                            }
                        }
                    }
                }
            });
        }

        var table = $('#revenue-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('reports.revenue.data') }}',
                data: function (d) {
                    d.branch_id = selectedBranchId;
                    var sd = $('#start_date').val();
                    var ed = $('#end_date').val();
                    d.start_date = sd ? moment(sd, 'DD/MM/YYYY').format('YYYY-MM-DD') : '';
                    d.end_date   = ed ? moment(ed, 'DD/MM/YYYY').format('YYYY-MM-DD') : '';
                }
            },
            columns: [
                { data: 'order_id', name: 'order_id' },
                { data: 'paid_at', name: 'paid_at' },
                { data: 'branch_name', name: 'branch.name' },
                { data: 'package_name', name: 'package.name' },
                { data: 'payment_method', name: 'payment_method' },
                { data: 'amount', name: 'amount', className: 'text-end fw-bold' },
            ],
            order: [[1, 'desc']],
            language: {
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div>',
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data',
                infoFiltered: '(difilter dari _MAX_ total data)',
                zeroRecords: 'Data tidak ditemukan',
                paginate: { first: 'Pertama', last: 'Terakhir', next: '&raquo;', previous: '&laquo;' },
            },
            responsive: true,
            pageLength: 10,
        });

        $('#btn-filter').on('click', function () {
            table.draw();
        });

        function buildExportUrl(base) {
            var params = new URLSearchParams();
            if (selectedBranchId) { params.set('branch_id', selectedBranchId); }
            var year = {{ $year }};
            if (year) { params.set('year', year); }
            var sd = $('#start_date').val();
            var ed = $('#end_date').val();
            if (sd) { params.set('start_date', moment(sd, 'DD/MM/YYYY').format('YYYY-MM-DD')); }
            if (ed) { params.set('end_date', moment(ed, 'DD/MM/YYYY').format('YYYY-MM-DD')); }
            var qs = params.toString();
            return base + (qs ? '?' + qs : '');
        }

        $('#export-excel').on('click', function (e) {
            e.preventDefault();
            window.location.href = buildExportUrl('{{ route('reports.revenue.export.excel') }}');
        });

        $('#export-pdf').on('click', function (e) {
            e.preventDefault();
            window.location.href = buildExportUrl('{{ route('reports.revenue.export.pdf') }}');
        });
    });
</script>
@endpush
