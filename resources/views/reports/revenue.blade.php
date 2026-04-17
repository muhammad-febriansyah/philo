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

    <div class="row g-4 mb-4">
        <div class="col-xl-4 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-soft-primary d-flex align-items-center justify-content-center text-primary">
                                <i class="mdi mdi-wallet-outline font-size-24"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1">Total Revenue</p>
                            <h4 class="mb-0">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-soft-success d-flex align-items-center justify-content-center text-success">
                                <i class="mdi mdi-calendar-month-outline font-size-24"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1">Bulan Ini</p>
                            <h4 class="mb-0">Rp {{ number_format($thisMonthRevenue, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-soft-info d-flex align-items-center justify-content-center text-info">
                                <i class="mdi mdi-clock-outline font-size-24"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1">Hari Ini</p>
                            <h4 class="mb-0">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</h4>
                        </div>
                    </div>
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
