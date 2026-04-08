@extends('layouts.admin')

@section('title', 'Pengguna')
@section('page-title', 'Pengguna')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Pengguna</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Daftar Pengguna</h5>
                    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm waves-effect">
                        <i class="mdi mdi-plus me-1"></i> Tambah Pengguna
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="users-table" class="table table-hover align-middle mb-0 dt-responsive nowrap w-100">
                            <thead class="table-light">
                                <tr>
                                    <th width="40">#</th>
                                    <th width="55">Avatar</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th width="100">Role</th>
                                    <th>Cabang</th>
                                    <th>Telepon</th>
                                    <th width="100">Aksi</th>
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
    var table = $('#users-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("users.data") }}',
            type: 'GET',
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'avatar', orderable: false, searchable: false, className: 'text-center' },
            { data: 'name' },
            { data: 'email' },
            { data: 'role_badge', orderable: false, className: 'text-center' },
            { data: 'branch_name', orderable: false },
            { data: 'phone', orderable: false },
            { data: 'actions', orderable: false, searchable: false, className: 'text-center' },
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
        pageLength: 10,
    });

    $('#users-table').on('click', '.btn-delete', function () {
        var name = $(this).data('name');
        var url  = $(this).data('url');

        Swal.fire({
            title: 'Hapus Pengguna?',
            html: 'Pengguna <strong>' + name + '</strong> akan dihapus secara permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="mdi mdi-trash-can me-1"></i> Ya, Hapus',
            cancelButtonText: 'Batal',
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        table.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 2000, showConfirmButton: false });
                    },
                    error: function () {
                        Swal.fire({ icon: 'error', title: 'Gagal!', text: 'Terjadi kesalahan, silakan coba lagi.' });
                    },
                });
            }
        });
    });
});
</script>
@endpush
