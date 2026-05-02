@extends('layouts.admin')

@section('title', 'Voucher Diskon')
@section('page-title', 'Voucher Diskon')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Voucher</li>
@endsection

@section('content')
<div class="row mb-3 g-3">
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="d-inline-flex align-items-center justify-content-center rounded-3"
                      style="width:42px;height:42px;background:#fef3c7;color:#92400e;">
                    <i class="fas fa-ticket-alt" style="font-size:20px;"></i>
                </span>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing:.04em;">Total Voucher</div>
                    <div class="h4 fw-bold mb-0" style="font-variant-numeric: tabular-nums;">{{ number_format($stats['total']) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="d-inline-flex align-items-center justify-content-center rounded-3"
                      style="width:42px;height:42px;background:#dcfce7;color:#166534;">
                    <i class="fas fa-check-circle" style="font-size:20px;"></i>
                </span>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing:.04em;">Aktif Sekarang</div>
                    <div class="h4 fw-bold mb-0" style="font-variant-numeric: tabular-nums;">{{ number_format($stats['active']) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="d-inline-flex align-items-center justify-content-center rounded-3"
                      style="width:42px;height:42px;background:#dbeafe;color:#1e40af;">
                    <i class="fas fa-shopping-cart" style="font-size:20px;"></i>
                </span>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing:.04em;">Total Redeem</div>
                    <div class="h4 fw-bold mb-0" style="font-variant-numeric: tabular-nums;">{{ number_format($stats['redeemed']) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="d-inline-flex align-items-center justify-content-center rounded-3"
                      style="width:42px;height:42px;background:#ede9fe;color:#5b21b6;">
                    <i class="fas fa-sync-alt" style="font-size:20px;"></i>
                </span>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing:.04em;">Auto Next-Visit</div>
                    <div class="h4 fw-bold mb-0" style="font-variant-numeric: tabular-nums;">{{ number_format($stats['auto_next_visit']) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3" style="border-top: 3px solid #C9A800 !important;">
    <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <span class="d-flex align-items-center justify-content-center rounded-2"
                  style="width:32px;height:32px;background:#fff9e0;border:1.5px solid #C9A800;">
                <i class="fas fa-filter" style="color:#C9A800;font-size:14px;"></i>
            </span>
            <h5 class="card-title mb-0 fw-semibold">Filter Voucher</h5>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-sm btn-outline-dark waves-effect" id="btn-bulk-print">
                <i class="fas fa-print me-1"></i> Bulk Print
            </button>
            <a href="{{ route('vouchers.bulk.create') }}" class="btn btn-sm btn-outline-dark waves-effect">
                <i class="fas fa-box-open me-1"></i> Bulk Generate
            </a>
            <a href="{{ route('vouchers.create') }}" class="btn btn-sm waves-effect"
               style="background:#C9A800;color:#1a1200;font-weight:600;border:none;">
                <i class="fas fa-plus me-1"></i> Voucher Baru
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-4 col-sm-6">
                <label class="form-label text-dark small fw-bold mb-1">CARI KODE / NAMA</label>
                <input type="search" id="filter-q" class="form-control bg-light border-0" placeholder="contoh: PHILO-XX" value="{{ $filters['q'] }}">
            </div>
            <div class="col-md-2 col-sm-6">
                <label class="form-label text-dark small fw-bold mb-1">STATUS</label>
                <select id="filter-status" class="form-select bg-light border-0">
                    <option value="">Semua</option>
                    <option value="active" @selected($filters['status']==='active')>Aktif</option>
                    <option value="inactive" @selected($filters['status']==='inactive')>Nonaktif</option>
                    <option value="expired" @selected($filters['status']==='expired')>Kedaluwarsa</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <label class="form-label text-dark small fw-bold mb-1">TIPE</label>
                <select id="filter-type" class="form-select bg-light border-0">
                    <option value="">Semua</option>
                    <option value="percentage" @selected($filters['type']==='percentage')>Persentase</option>
                    <option value="fixed" @selected($filters['type']==='fixed')>Nominal</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <label class="form-label text-dark small fw-bold mb-1">SUMBER</label>
                <select id="filter-source" class="form-select bg-light border-0">
                    <option value="">Semua</option>
                    <option value="manual" @selected($filters['source']==='manual')>Manual</option>
                    <option value="bulk" @selected($filters['source']==='bulk')>Bulk</option>
                    <option value="auto_next_visit" @selected($filters['source']==='auto_next_visit')>Auto Next-Visit</option>
                </select>
            </div>
            <div class="col-md-auto">
                <button type="button" class="btn btn-outline-secondary waves-effect" id="btn-reset-filter">
                    <i class="fas fa-undo me-1"></i> Reset
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table id="vouchers-table" class="table table-hover align-middle mb-0 dt-responsive nowrap w-100">
                <thead class="table-light">
                    <tr>
                        <th width="36">
                            <input type="checkbox" class="form-check-input" id="select-all-vouchers" aria-label="Pilih semua voucher">
                        </th>
                        <th width="40">#</th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Tipe / Nilai</th>
                        <th>Pemakaian</th>
                        <th>Periode</th>
                        <th>Sumber</th>
                        <th>Status</th>
                        <th width="220">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('vouchers.print.bulk') }}" id="bulk-print-form" target="_blank" class="d-none">
    @csrf
</form>
@endsection

@push('scripts')
<script>
$(function () {
    var table = $('#vouchers-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        order: [],
        ajax: {
            url: '{{ route('vouchers.data') }}',
            data: function (d) {
                d.q = $('#filter-q').val();
                d.status = $('#filter-status').val();
                d.type = $('#filter-type').val();
                d.source = $('#filter-source').val();
            }
        },
        columns: [
            { data: 'select', orderable: false, searchable: false },
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'code', name: 'code', render: function (d) { return '<code class="fw-bold" style="background:#fef3c7;color:#92400e;padding:.2rem .5rem;border-radius:6px;">'+d+'</code>'; } },
            { data: 'name', name: 'name', defaultContent: '—' },
            { data: 'value_formatted', name: 'value', orderable: false },
            { data: 'uses_text', name: 'uses_count', orderable: false },
            { data: 'validity', name: 'valid_until', orderable: false },
            { data: 'source_badge', name: 'source', orderable: false },
            { data: 'status_badge', name: 'is_active', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        drawCallback: function () {
            $('#select-all-vouchers').prop('checked', false);
        }
    });

    var debounceTimer;
    $('#filter-q').on('keyup', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () { table.ajax.reload(); }, 300);
    });
    $('#filter-status, #filter-type, #filter-source').on('change', function () { table.ajax.reload(); });
    $('#btn-reset-filter').on('click', function () {
        $('#filter-q').val('');
        $('#filter-status, #filter-type, #filter-source').val('');
        table.ajax.reload();
    });

    $('#select-all-vouchers').on('change', function () {
        $('.voucher-select').prop('checked', $(this).is(':checked'));
    });

    $(document).on('change', '.voucher-select', function () {
        var total = $('.voucher-select').length;
        var checked = $('.voucher-select:checked').length;
        $('#select-all-vouchers').prop('checked', total > 0 && total === checked);
    });

    $(document).on('click', '.btn-copy', function () {
        var code = $(this).data('code');
        navigator.clipboard.writeText(code).then(function () {
            Swal.fire({ icon: 'success', title: 'Disalin', text: code, timer: 1200, showConfirmButton: false, toast: true, position: 'top-end' });
        });
    });

    $('#btn-bulk-print').on('click', function () {
        var ids = $('.voucher-select:checked').map(function () {
            return $(this).val();
        }).get();

        if (!ids.length) {
            Swal.fire({
                icon: 'warning',
                title: 'Belum ada voucher dipilih',
                text: 'Pilih minimal satu voucher untuk bulk print.'
            });
            return;
        }

        var $form = $('#bulk-print-form');
        $form.find('input[name="voucher_ids[]"]').remove();

        ids.forEach(function (id) {
            $('<input>', {
                type: 'hidden',
                name: 'voucher_ids[]',
                value: id
            }).appendTo($form);
        });

        $form.trigger('submit');
    });

    $(document).on('click', '.btn-delete', function () {
        var name = $(this).data('name');
        var url = $(this).data('url');
        Swal.fire({
            title: 'Hapus Voucher?',
            html: 'Voucher <strong>'+name+'</strong> akan dihapus permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#71717a',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                url: url,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function (res) {
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
                    table.ajax.reload(null, false);
                },
                error: function () { Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal menghapus voucher.' }); }
            });
        });
    });
});
</script>
@endpush
