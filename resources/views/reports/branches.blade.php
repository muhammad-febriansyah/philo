@extends('layouts.admin')

@section('title', 'Laporan per Cabang')
@section('page-title', 'Per Cabang')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Per Cabang</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between mb-4">
                        <div>
                            <h4 class="card-title mb-1">Breakdown Pendapatan per Cabang</h4>
                            <p class="text-muted mb-0">Ringkasan revenue dan jumlah transaksi sukses untuk setiap cabang.</p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 dt-responsive nowrap w-100" id="branches-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Cabang</th>
                                    <th>Kode</th>
                                    <th class="text-center">Total Transaksi</th>
                                    <th class="text-end">Total Revenue</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($branches as $branch)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if ($branch->photo)
                                                    <img src="{{ Storage::url($branch->photo) }}" class="rounded-circle me-2" width="40" height="40" alt="{{ $branch->name }}">
                                                @else
                                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                                        <i class="mdi mdi-store font-size-18 text-muted"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="fw-semibold">{{ $branch->name }}</div>
                                                    <small class="text-muted">Cabang aktif</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><code>{{ $branch->code }}</code></td>
                                        <td class="text-center">
                                            <span class="badge bg-soft-info text-info">{{ number_format($branch->transactions_count) }}</span>
                                        </td>
                                        <td class="text-end fw-bold">Rp {{ number_format($branch->total_revenue, 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('reports.revenue', ['branch_id' => $branch->id]) }}" class="btn btn-sm btn-light waves-effect">
                                                <i class="mdi mdi-eye-outline me-1"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">Belum ada data cabang.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="2">TOTAL</th>
                                    <th class="text-center">{{ number_format($branches->sum('transactions_count')) }}</th>
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

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Visualisasi Revenue per Cabang</h4>
                    <div style="height: 300px;">
                        <canvas id="branchChart"></canvas>
                    </div>
                    @if ($branches->sum('total_revenue') === 0)
                        <div class="text-center text-muted mt-3">Belum ada revenue yang bisa divisualisasikan.</div>
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
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/libs/chart.js/Chart.bundle.min.js') }}"></script>
<script>
    $(function () {
        var ctx = document.getElementById('branchChart').getContext('2d');
        var branches = @json($branches->pluck('name'));
        var revenues = @json($branches->pluck('total_revenue'));

        $('#branches-table').DataTable({
            ordering: false,
            responsive: true,
            pageLength: 10,
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

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: branches,
                datasets: [{
                    label: 'Total Revenue',
                    data: revenues,
                    backgroundColor: 'rgba(52, 195, 143, 0.5)',
                    borderColor: 'rgba(52, 195, 143, 1)',
                    borderWidth: 1
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
    });
</script>
@endpush
