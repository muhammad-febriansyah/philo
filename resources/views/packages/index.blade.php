@extends('layouts.admin')

@section('title', 'Paket Foto')
@section('page-title', 'Paket Foto')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Paket Foto</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">Daftar Paket Foto</h5>
                <a href="{{ route('packages.create') }}" class="btn btn-primary btn-sm waves-effect">
                    <i class="mdi mdi-plus me-1"></i> Tambah Paket
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="packages-table" class="table table-hover align-middle mb-0 dt-responsive nowrap w-100">
                        <thead class="table-light">
                            <tr>
                                <th width="40">#</th>
                                <th>Nama Paket</th>
                                <th class="text-center">Jumlah Foto</th>
                                <th class="text-center">Ukuran</th>
                                <th class="text-end">Harga</th>
                                <th width="90" class="text-center">Status</th>
                                <th width="220" class="text-center">Aksi</th>
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
    var table = $('#packages-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("packages.data") }}',
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
            {data: 'name', name: 'name'},
            {data: 'photo_count', name: 'photo_count', className: 'text-center'},
            {data: 'print_size', name: 'print_size', className: 'text-center'},
            {data: 'price_formatted', name: 'price', className: 'text-end'},
            {data: 'status', name: 'status', className: 'text-center'},
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
        responsive: true
    });

    $('#packages-table').on('click', '.btn-delete', function() {
        var name = $(this).data('name');
        var url = $(this).data('url');

        Swal.fire({
            title: 'Hapus Paket?',
            html: 'Paket <strong>' + name + '</strong> akan dihapus secara permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
        }).then(function(result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        table.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 2000, showConfirmButton: false });
                    },
                    error: function() {
                        Swal.fire({ icon: 'error', title: 'Gagal!', text: 'Terjadi kesalahan saat menghapus data.' });
                    }
                });
            }
        });
    });
});
</script>
@endpush
