@extends('layouts.admin')

@section('title', 'Template / Frame')
@section('page-title', 'Template / Frame')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Template / Frame</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">Daftar Template / Frame</h5>
                <a href="{{ route('templates.create') }}" class="btn btn-primary btn-sm waves-effect">
                    <i class="mdi mdi-plus me-1"></i> Tambah Template
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="templates-table" class="table table-hover align-middle mb-0 dt-responsive nowrap w-100">
                        <thead class="table-light">
                            <tr>
                                <th width="40">#</th>
                                <th width="60">Preview</th>
                                <th>Nama Template</th>
                                <th width="90" class="text-center">Ukuran</th>
                                <th width="80" class="text-center">Jumlah Slot</th>
                                <th width="90" class="text-center">Status</th>
                                <th width="130" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    var table = $('#templates-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("templates.data") }}',
        columns: [
            {data: 'DT_RowIndex',    name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
            {data: 'thumbnail_html', name: 'thumbnail_path', orderable: false, searchable: false, className: 'text-center'},
            {data: 'name',           name: 'name'},
            {data: 'print_size',     name: 'print_size', className: 'text-center'},
            {data: 'photo_slots',    name: 'photo_slots', className: 'text-center'},
            {data: 'status',         name: 'is_active', orderable: false, searchable: false, className: 'text-center'},
            {data: 'actions',        name: 'actions', orderable: false, searchable: false, className: 'text-center'},
        ],
        language: {
            processing:   '<div class="spinner-border spinner-border-sm text-primary" role="status"></div>',
            search:       'Cari:',
            lengthMenu:   'Tampilkan _MENU_ data',
            info:         'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            infoEmpty:    'Tidak ada data',
            infoFiltered: '(difilter dari _MAX_ total data)',
            zeroRecords:  'Data tidak ditemukan',
            paginate: { first: 'Pertama', last: 'Terakhir', next: '&raquo;', previous: '&laquo;' },
        },
        responsive: true,
    });

    /* ─── Delete (tetap AJAX dari list) ─── */
    $('#templates-table').on('click', '.btn-delete', function () {
        var name = $(this).data('name');
        var url  = $(this).data('url');

        Swal.fire({
            title: 'Hapus Template?',
            html: 'Template <strong>' + name + '</strong> dan file frame-nya akan dihapus permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="mdi mdi-trash-can me-1"></i> Ya, Hapus',
            cancelButtonText: 'Batal',
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    url:  url,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        table.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: 'Dihapus!', text: res.message, timer: 2000, showConfirmButton: false });
                    },
                    error: function () {
                        Swal.fire({ icon: 'error', title: 'Gagal!', text: 'Terjadi kesalahan saat menghapus.' });
                    },
                });
            }
        });
    });
});
</script>
@endpush
