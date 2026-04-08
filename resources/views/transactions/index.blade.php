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
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-bottom">
                <h5 class="card-title mb-0">Filter Transaksi</h5>
            </div>
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-3 mb-3 mb-md-0">
                        <label class="form-label text-dark small fw-bold">CABANG</label>
                        <select id="filter-branch" class="form-select bg-light border-0">
                            <option value="">Semua Cabang</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3 mb-md-0">
                        <label class="form-label text-dark small fw-bold">STATUS PEMBAYARAN</label>
                        <select id="filter-status" class="form-select bg-light border-0">
                            <option value="">Semua Status</option>
                            <option value="pending">PENDING</option>
                            <option value="paid">PAID</option>
                            <option value="expired">EXPIRED</option>
                            <option value="failed">FAILED</option>
                            <option value="cancelled">CANCELLED</option>
                        </select>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <button type="button" class="btn btn-soft-primary waves-effect" id="btn-reset-filter">
                            <i class="mdi mdi-filter-off-outline me-1"></i> Reset Filter
                        </button>
                    </div>
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
                            <tr><th class="text-muted fw-normal">Status</th><td>: <span id="det-status"></span></td></tr>
                            <tr><th class="text-muted fw-normal">Waktu</th><td>: <span id="det-time"></span></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6 mb-4 border-start border-light ps-md-4">
                        <h6 class="text-primary fw-bold text-uppercase font-size-12 mb-3">Rincian Pembayaran</h6>
                        <table class="table table-sm table-borderless mb-0">
                            <tr><th width="130" class="text-muted fw-normal">Total Bayar</th><td class="text-primary fw-bold">: <span id="det-amount"></span></td></tr>
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

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script>
$(function () {
    var table = $('#transactions-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("transactions.data") }}',
            data: function(d) {
                d.branch_id = $('#filter-branch').val();
                d.status = $('#filter-status').val();
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
        order: [[6, 'desc']]
    });

    $('#filter-branch, #filter-status').on('change', function() {
        table.draw();
    });

    $('#btn-reset-filter').on('click', function() {
        $('#filter-branch, #filter-status').val('');
        table.draw();
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
            
            $('#det-amount').text('Rp ' + new Intl.NumberFormat('id-ID').format(data.amount));
            $('#det-method').text(data.payment_method ? data.payment_method.toUpperCase() : '-');
            $('#det-ref').text(data.duitku_reference || '-');
            $('#det-paid-at').text(data.paid_at ? moment(data.paid_at).format('DD/MM/YYYY HH:mm:ss') : '-');
            
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
