@extends('layouts.admin')

@section('title', 'Daftar Transaksi')
@section('page-title', 'Daftar Transaksi')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Transaksi</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Filter + Export Card -->
        <div class="card border-0 shadow-sm mb-4" style="border-top: 3px solid #C9A800 !important;">
            <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="d-flex align-items-center justify-content-center rounded-2 me-1"
                          style="width:32px;height:32px;background:#fff9e0;border:1.5px solid #C9A800;">
                        <i class="mdi mdi-filter-outline" style="color:#C9A800;font-size:16px;"></i>
                    </span>
                    <h5 class="card-title mb-0 fw-semibold">Filter & Export Transaksi</h5>
                </div>
                <div class="d-flex gap-2 flex-wrap" id="export-buttons">
                    <a id="btn-export-excel"
                       href="{{ route('transactions.export.excel') }}"
                       class="btn btn-sm waves-effect d-flex align-items-center gap-1"
                       style="background:#C9A800;color:#1a1200;font-weight:600;border:none;">
                        <i class="mdi mdi-microsoft-excel font-size-16"></i>
                        <span>Export Excel</span>
                    </a>
                    <a id="btn-export-pdf"
                       href="{{ route('transactions.export.pdf') }}"
                       class="btn btn-sm waves-effect d-flex align-items-center gap-1"
                       style="background:#1a1200;color:#C9A800;font-weight:600;border:none;">
                        <i class="mdi mdi-file-pdf-box font-size-16"></i>
                        <span>Export PDF</span>
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row align-items-end g-3">
                    @if(!$isCabangUser && $branches->isNotEmpty())
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label text-dark small fw-bold mb-1">CABANG</label>
                        <select id="filter-branch" class="form-select bg-light border-0">
                            <option value="">Semua Cabang</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label text-dark small fw-bold mb-1">STATUS PEMBAYARAN</label>
                        <select id="filter-status" class="form-select bg-light border-0">
                            <option value="">Semua Status</option>
                            <option value="pending">PENDING</option>
                            <option value="paid">PAID</option>
                            <option value="expired">EXPIRED</option>
                            <option value="failed">FAILED</option>
                            <option value="cancelled">CANCELLED</option>
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <label class="form-label text-dark small fw-bold mb-1">DARI TANGGAL</label>
                        <div class="input-group">
                            <input type="text" id="filter-start-date" class="form-control bg-light border-0 datepicker" placeholder="dd/mm/yyyy" autocomplete="off">
                            <span class="input-group-text bg-light border-0"><i class="mdi mdi-calendar-outline"></i></span>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <label class="form-label text-dark small fw-bold mb-1">SAMPAI TANGGAL</label>
                        <div class="input-group">
                            <input type="text" id="filter-end-date" class="form-control bg-light border-0 datepicker" placeholder="dd/mm/yyyy" autocomplete="off">
                            <span class="input-group-text bg-light border-0"><i class="mdi mdi-calendar-outline"></i></span>
                        </div>
                    </div>
                    <div class="col-md-auto">
                        <button type="button" class="btn btn-outline-secondary waves-effect" id="btn-reset-filter">
                            <i class="mdi mdi-filter-off-outline me-1"></i> Reset
                        </button>
                    </div>

                    {{-- Quick date presets --}}
                    <div class="col-12">
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <span class="small text-muted fw-semibold">PRESET CEPAT:</span>
                            <button type="button" class="btn btn-sm btn-light date-preset" data-preset="today">Hari Ini</button>
                            <button type="button" class="btn btn-sm btn-light date-preset" data-preset="yesterday">Kemarin</button>
                            <button type="button" class="btn btn-sm btn-light date-preset" data-preset="7days">7 Hari Terakhir</button>
                            <button type="button" class="btn btn-sm btn-light date-preset" data-preset="30days">30 Hari Terakhir</button>
                            <button type="button" class="btn btn-sm btn-light date-preset" data-preset="this-month">Bulan Ini</button>
                            <button type="button" class="btn btn-sm btn-light date-preset" data-preset="last-month">Bulan Lalu</button>
                        </div>
                    </div>

                    @if($isCabangUser && $branches->isNotEmpty())
                    <div class="col-12">
                        <div class="alert alert-light border mb-0 py-2 px-3">
                            <span class="fw-semibold">Akses cabang aktif:</span>
                            {{ $branches->first()->name }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="transactions-table" class="table table-hover align-middle mb-0 dt-responsive nowrap w-100">
                        <thead class="table-light">
                            <tr>
                                <th width="40">#</th>
                                <th>Order ID</th>
                                <th>Cabang</th>
                                <th>Paket</th>
                                <th>Total</th>
                                <th width="90">Status</th>
                                <th width="150">Waktu Transaksi</th>
                                <th width="60" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="modal-detail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content text-dark border-0 shadow-lg">
            <div class="modal-header bg-light">
                <h5 class="modal-title"><i class="mdi mdi-file-document-box-outline me-1"></i> Detail Transaksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <h6 class="text-primary fw-bold text-uppercase font-size-12 mb-3">Informasi Pesanan</h6>
                        <table class="table table-sm table-borderless mb-0">
                            <tr><th width="130" class="text-muted fw-normal">Order ID</th><td class="fw-bold">: <span id="det-order-id"></span></td></tr>
                            <tr><th class="text-muted fw-normal">Cabang</th><td>: <span id="det-branch"></span></td></tr>
                            <tr><th class="text-muted fw-normal">Paket Foto</th><td>: <span id="det-package"></span></td></tr>
                            <tr><th class="text-muted fw-normal">Jumlah Cetak</th><td>: <span id="det-prints"></span></td></tr>
                            <tr><th class="text-muted fw-normal">Status</th><td>: <span id="det-status"></span></td></tr>
                            <tr><th class="text-muted fw-normal">Waktu</th><td>: <span id="det-time"></span></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6 mb-4 border-start border-light ps-md-4">
                        <h6 class="text-primary fw-bold text-uppercase font-size-12 mb-3">Rincian Pembayaran</h6>
                        <table class="table table-sm table-borderless mb-0">
                            <tr id="det-row-original" class="d-none"><th width="130" class="text-muted fw-normal">Harga Asli</th><td>: <span id="det-original" class="text-muted text-decoration-line-through"></span></td></tr>
                            <tr id="det-row-voucher" class="d-none"><th class="text-muted fw-normal">Voucher</th><td>: <span id="det-voucher"></span></td></tr>
                            <tr id="det-row-discount" class="d-none"><th class="text-muted fw-normal">Diskon</th><td class="text-success">: − <span id="det-discount"></span></td></tr>
                            <tr><th class="text-muted fw-normal">Total Bayar</th><td class="text-primary fw-bold">: <span id="det-amount"></span></td></tr>
                            <tr><th class="text-muted fw-normal">Metode Pembayaran</th><td>: <span id="det-method"></span></td></tr>
                            <tr><th class="text-muted fw-normal">Ref Pembayaran</th><td>: <span id="det-ref" class="font-size-12"></span></td></tr>
                            <tr><th class="text-muted fw-normal">Waktu Bayar</th><td>: <span id="det-paid-at"></span></td></tr>
                        </table>
                    </div>
                </div>
                
                <div id="session-info" class="mt-2 p-3 bg-light rounded border border-info border-opacity-25 d-none">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <i class="mdi mdi-camera-check font-size-24 text-info"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 text-info">Sesi Foto Selesai</h6>
                            <p class="text-muted mb-0 font-size-12">Sesi foto untuk transaksi ini telah diselesaikan pada <span id="det-session-time" class="fw-bold text-dark"></span>.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-3 bg-light bg-opacity-50">
                <button type="button" class="btn btn-secondary px-4 waves-effect rounded-pill" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}">
<style>
    .date-preset {
        background: #fff9e0;
        border: 1px solid #f0e0a8;
        color: #8a6d00;
        font-size: 0.78rem;
        font-weight: 600;
        padding: 0.3rem 0.7rem;
        transition: all 0.15s;
    }
    .date-preset:hover {
        background: #C9A800;
        color: #1a1200;
        border-color: #C9A800;
    }
    .date-preset.active {
        background: #1a1200;
        color: #C9A800;
        border-color: #1a1200;
    }
</style>
@endpush

@push('scripts')
<link rel="stylesheet" href="{{ asset('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}">
<script src="{{ asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('assets/libs/bootstrap-datepicker/locales/bootstrap-datepicker.id.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script>
$(function () {
    // Init datepickers
    $('.datepicker').datepicker({
        format: 'dd/mm/yyyy',
        autoclose: true,
        todayHighlight: true,
        language: 'id',
        clearBtn: true,
    });

    $('#filter-start-date').on('changeDate', function () {
        $('#filter-end-date').datepicker('setStartDate', $(this).datepicker('getDate'));
    });

    function getDateParam(selector) {
        var val = $(selector).val();
        return val ? moment(val, 'DD/MM/YYYY').format('YYYY-MM-DD') : '';
    }

    var table = $('#transactions-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("transactions.data") }}',
            data: function(d) {
                d.branch_id = $('#filter-branch').val();
                d.status = $('#filter-status').val();
                d.start_date = getDateParam('#filter-start-date');
                d.end_date = getDateParam('#filter-end-date');
            }
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
            {data: 'order_id', name: 'order_id'},
            {data: 'branch_name', name: 'branch.name'},
            {data: 'package_name', name: 'package.name'},
            {data: 'amount_formatted', name: 'amount', className: 'text-end fw-bold text-primary'},
            {data: 'status_badge', name: 'status', className: 'text-center'},
            {data: 'created_at', name: 'created_at', render: function(data) {
                return '<span class="text-muted"><i class="mdi mdi-clock-outline me-1"></i>' + moment(data).format('DD/MM/YYYY HH:mm') + '</span>';
            }},
            {data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center'},
        ],
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
        order: [[6, 'desc']],
        search: { search: '{{ addslashes(request("q", "")) }}' }
    });

    @if(request('q'))
    // Highlight bahwa pencarian aktif dari search bar
    table.search('{{ addslashes(request("q")) }}').draw();
    @endif

    function updateExportLinks() {
        var params = new URLSearchParams();
        var branch = $('#filter-branch').val();
        var status = $('#filter-status').val();
        var startDate = getDateParam('#filter-start-date');
        var endDate = getDateParam('#filter-end-date');
        var q = '{{ addslashes(request("q", "")) }}';

        if (branch) params.set('branch_id', branch);
        if (status) params.set('status', status);
        if (startDate) params.set('start_date', startDate);
        if (endDate) params.set('end_date', endDate);
        if (q) params.set('q', q);

        var qs = params.toString() ? '?' + params.toString() : '';
        $('#btn-export-excel').attr('href', '{{ route("transactions.export.excel") }}' + qs);
        $('#btn-export-pdf').attr('href', '{{ route("transactions.export.pdf") }}' + qs);
    }

    updateExportLinks();

    function applyFilter() {
        table.draw();
        updateExportLinks();
    }

    $('#filter-branch, #filter-status').on('change', applyFilter);
    $('#filter-start-date, #filter-end-date').on('changeDate clearDate', function () {
        $('.date-preset').removeClass('active');
        applyFilter();
    });

    $('#btn-reset-filter').on('click', function() {
        $('#filter-branch, #filter-status').val('');
        $('#filter-start-date, #filter-end-date').datepicker('clearDates');
        $('.date-preset').removeClass('active');
        applyFilter();
    });

    // Quick date presets
    $('.date-preset').on('click', function() {
        var preset = $(this).data('preset');
        var start, end;
        var today = moment();

        switch (preset) {
            case 'today':
                start = end = today.clone();
                break;
            case 'yesterday':
                start = end = today.clone().subtract(1, 'days');
                break;
            case '7days':
                start = today.clone().subtract(6, 'days');
                end = today.clone();
                break;
            case '30days':
                start = today.clone().subtract(29, 'days');
                end = today.clone();
                break;
            case 'this-month':
                start = today.clone().startOf('month');
                end = today.clone().endOf('month');
                break;
            case 'last-month':
                start = today.clone().subtract(1, 'month').startOf('month');
                end = today.clone().subtract(1, 'month').endOf('month');
                break;
        }

        $('.date-preset').removeClass('active');
        $(this).addClass('active');
        $('#filter-start-date').datepicker('setDate', start.format('DD/MM/YYYY'));
        $('#filter-end-date').datepicker('setDate', end.format('DD/MM/YYYY'));
        applyFilter();
    });

    $('#transactions-table').on('click', '.btn-detail', function() {
        var id = $(this).data('id');
        $.get('{{ route("transactions.index") }}/' + id, function(data) {
            $('#det-order-id').text(data.order_id);
            $('#det-branch').text(data.branch ? data.branch.name : '-');
            $('#det-package').text(data.package ? data.package.name : '-');
            $('#det-time').text(moment(data.created_at).format('DD/MM/YYYY HH:mm:ss'));
            
            var badges = { 'pending': 'warning', 'paid': 'success', 'expired': 'danger', 'failed': 'dark', 'cancelled': 'secondary' };
            var color = badges[data.status] || 'secondary';
            $('#det-status').html('<span class="badge bg-' + color + '">' + data.status.toUpperCase() + '</span>');
            
            var totalPrints = 1 + (parseInt(data.extra_prints || 0, 10));
            var printsText = totalPrints + ' lembar';
            if (data.extra_prints > 0) {
                printsText += ' (1 default + ' + data.extra_prints + ' tambahan)';
            }
            $('#det-prints').text(printsText);

            $('#det-amount').text('Rp ' + new Intl.NumberFormat('id-ID').format(data.amount));
            $('#det-method').text(data.payment_method ? data.payment_method.toUpperCase() : '-');
            $('#det-ref').text(data.duitku_reference || '-');
            $('#det-paid-at').text(data.paid_at ? moment(data.paid_at).format('DD/MM/YYYY HH:mm:ss') : '-');

            if (data.voucher) {
                $('#det-row-voucher').removeClass('d-none');
                var label = data.voucher.code;
                if (data.voucher.name) label += ' — ' + data.voucher.name;
                $('#det-voucher').html('<code class="bg-warning bg-opacity-10 text-warning px-2 py-1 rounded">' + label + '</code>');

                if (data.discount_amount && parseFloat(data.discount_amount) > 0) {
                    $('#det-row-discount').removeClass('d-none');
                    $('#det-discount').text('Rp ' + new Intl.NumberFormat('id-ID').format(parseFloat(data.discount_amount)));
                } else {
                    $('#det-row-discount').addClass('d-none');
                }

                if (data.original_amount && parseFloat(data.original_amount) > 0) {
                    $('#det-row-original').removeClass('d-none');
                    $('#det-original').text('Rp ' + new Intl.NumberFormat('id-ID').format(parseFloat(data.original_amount)));
                } else {
                    $('#det-row-original').addClass('d-none');
                }
            } else {
                $('#det-row-voucher').addClass('d-none');
                $('#det-row-discount').addClass('d-none');
                $('#det-row-original').addClass('d-none');
            }
            
            if (data.photo_session && data.photo_session.completed_at) {
                $('#session-info').removeClass('d-none');
                $('#det-session-time').text(moment(data.photo_session.completed_at).format('DD/MM/YYYY HH:mm:ss'));
            } else {
                $('#session-info').addClass('d-none');
            }
            
            $('#modal-detail').modal('show');
        });
    });
});
</script>
@endpush
