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
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">Daftar Paket Foto</h5>
                <button type="button" class="btn btn-primary btn-sm waves-effect btn-add">
                    <i class="mdi mdi-plus me-1"></i> Tambah Paket
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="packages-table" class="table table-hover align-middle mb-0 dt-responsive nowrap w-100">
                        <thead class="table-light">
                            <tr>
                                <th width="40">#</th>
                                <th>Nama Paket</th>
                                <th>Jumlah Foto</th>
                                <th>Ukuran</th>
                                <th>Harga</th>
                                <th width="90">Status</th>
                                <th width="120">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form -->
<div class="modal fade" id="modal-package" tabindex="-1" aria-labelledby="modal-package-title" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-package-title">Tambah Paket Foto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-package">
                @csrf
                <input type="hidden" name="_method" id="form-method" value="POST">
                <input type="hidden" name="id" id="package-id">
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Nama Paket <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Contoh: Paket Keluarga A" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Penjelasan singkat paket..."></textarea>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Jumlah Foto <span class="text-danger">*</span></label>
                            <input type="number" name="photo_count" class="form-control" placeholder="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ukuran <span class="text-danger">*</span></label>
                            <input type="text" name="print_size" class="form-control" placeholder="Contoh: 4R, 10R" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Harga <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="price" class="form-control" placeholder="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="package-active" value="1" checked>
                                <label class="form-check-label" for="package-active">Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary waves-effect waves-light" id="btn-save">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="modal-detail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Paket Foto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-sm mb-0 text-dark">
                    <tr>
                        <th width="150">Nama Paket</th>
                        <td>: <span id="detail-name"></span></td>
                    </tr>
                    <tr>
                        <th>Deskripsi</th>
                        <td>: <span id="detail-description"></span></td>
                    </tr>
                    <tr>
                        <th>Jumlah Foto</th>
                        <td>: <span id="detail-photo-count"></span></td>
                    </tr>
                    <tr>
                        <th>Ukuran</th>
                        <td>: <span id="detail-print-size"></span></td>
                    </tr>
                    <tr>
                        <th>Harga</th>
                        <td>: <span id="detail-price"></span></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>: <span id="detail-status"></span></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Tutup</button>
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

    // Reset Form
    function resetForm() {
        $('#form-package')[0].reset();
        $('#form-method').val('POST');
        $('#package-id').val('');
        $('#modal-package-title').text('Tambah Paket Foto');
        $('.is-invalid').removeClass('is-invalid');
    }

    // Modal Add
    $('.btn-add').on('click', function() {
        resetForm();
        $('#modal-package').modal('show');
    });

    // Modal Edit
    $('#packages-table').on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        resetForm();
        $('#modal-package-title').text('Edit Paket Foto');
        $('#form-method').val('PUT');
        
        $.get('{{ route("packages.index") }}/' + id, function(data) {
            $('#package-id').val(data.id);
            $('[name="name"]').val(data.name);
            $('[name="description"]').val(data.description);
            $('[name="photo_count"]').val(data.photo_count);
            $('[name="print_size"]').val(data.print_size);
            $('[name="price"]').val(data.price);
            $('#package-active').prop('checked', data.is_active);
            $('#modal-package').modal('show');
        });
    });

    // Modal Detail
    $('#packages-table').on('click', '.btn-detail', function() {
        var id = $(this).data('id');
        $.get('{{ route("packages.index") }}/' + id, function(data) {
            $('#detail-name').text(data.name);
            $('#detail-description').text(data.description || '-');
            $('#detail-photo-count').text(data.photo_count);
            $('#detail-print-size').text(data.print_size);
            $('#detail-price').text('Rp ' + new Intl.NumberFormat('id-ID').format(data.price));
            $('#detail-status').html(data.is_active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Nonaktif</span>');
            $('#modal-detail').modal('show');
        });
    });

    // Form Submit
    $('#form-package').on('submit', function(e) {
        e.preventDefault();
        $('#btn-save').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status"></span> Menyimpan...');
        
        var id = $('#package-id').val();
        var url = id ? '{{ route("packages.index") }}/' + id : '{{ route("packages.store") }}';
        
        $.ajax({
            url: url,
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                $('#modal-package').modal('hide');
                table.ajax.reload(null, false);
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 2000, showConfirmButton: false });
            },
            error: function(err) {
                if (err.status === 422) {
                    var errors = err.responseJSON.errors;
                    $('.is-invalid').removeClass('is-invalid');
                    Object.keys(errors).forEach(function(key) {
                        $('[name="' + key + '"]').addClass('is-invalid');
                    });
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: 'Mohon periksa kembali inputan Anda.' });
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: 'Terjadi kesalahan pada server.' });
                }
            },
            complete: function() {
                $('#btn-save').prop('disabled', false).text('Simpan');
            }
        });
    });

    // Delete
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
