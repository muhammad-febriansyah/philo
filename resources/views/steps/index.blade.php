@extends('layouts.admin')

@section('title', 'Cara Kerja')
@section('page-title', 'Cara Kerja')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Cara Kerja</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">Daftar Langkah Cara Kerja</h5>
                <button type="button" class="btn btn-primary btn-sm waves-effect btn-add">
                    <i class="mdi mdi-plus me-1"></i> Tambah Langkah
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="steps-table" class="table table-hover align-middle mb-0 dt-responsive nowrap w-100">
                        <thead class="table-light">
                            <tr>
                                <th width="40">#</th>
                                <th width="70" class="text-center">Nomor</th>
                                <th>Judul</th>
                                <th>Deskripsi</th>
                                <th width="70" class="text-center">Urutan</th>
                                <th width="90" class="text-center">Status</th>
                                <th width="150" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form -->
<div class="modal fade" id="modal-step" tabindex="-1" aria-labelledby="modal-step-title" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-step-title">Tambah Langkah Cara Kerja</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-step">
                @csrf
                <input type="hidden" name="_method" id="form-method" value="POST">
                <input type="hidden" name="id" id="step-id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nomor Langkah <span class="text-danger">*</span></label>
                        <input type="text" name="number" id="step-number" class="form-control"
                               placeholder="Contoh: 01" required maxlength="10">
                        <small class="text-muted">Angka yang ditampilkan di kartu langkah, misal: 01, 02, 1, 2.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Judul <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="step-title" class="form-control"
                               placeholder="Contoh: Daftar & Setup" required maxlength="150">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi <span class="text-danger">*</span></label>
                        <textarea name="description" id="step-description" class="form-control" rows="3"
                                  placeholder="Penjelasan singkat langkah ini..." required maxlength="500"></textarea>
                        <small class="text-muted"><span id="desc-count">0</span>/500 karakter</small>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Urutan Tampil</label>
                            <input type="number" name="sort_order" id="step-sort" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-6 d-flex align-items-end pb-1">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="step-active" value="1" checked>
                                <label class="form-check-label" for="step-active">Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary waves-effect waves-light" id="btn-save">
                        <i class="mdi mdi-content-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    var table = $('#steps-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("steps.data") }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'number', name: 'number', className: 'text-center fw-bold' },
            { data: 'title', name: 'title' },
            { data: 'description', name: 'description' },
            { data: 'sort_order', name: 'sort_order', className: 'text-center' },
            { data: 'status', name: 'is_active', orderable: false, searchable: false, className: 'text-center' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' },
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
    });

    // Description character count
    $('#step-description').on('input', function () {
        $('#desc-count').text($(this).val().length);
    });

    function resetForm() {
        $('#form-step')[0].reset();
        $('#form-method').val('POST');
        $('#step-id').val('');
        $('#modal-step-title').text('Tambah Langkah Cara Kerja');
        $('#desc-count').text('0');
        $('.is-invalid').removeClass('is-invalid');
        $('#step-active').prop('checked', true);
    }

    // Tambah
    $('.btn-add').on('click', function () {
        resetForm();
        $('#modal-step').modal('show');
    });

    // Edit
    $('#steps-table').on('click', '.btn-edit', function () {
        var btn = $(this);
        resetForm();
        $('#modal-step-title').text('Edit Langkah Cara Kerja');
        $('#form-method').val('PUT');
        $('#step-id').val(btn.data('id'));
        $('#step-number').val(btn.data('number'));
        $('#step-title').val(btn.data('title'));
        $('#step-description').val(btn.data('description'));
        $('#desc-count').text(String(btn.data('description')).length);
        $('#step-sort').val(btn.data('sort_order'));
        $('#step-active').prop('checked', btn.data('is_active') == '1');
        $('#modal-step').modal('show');
    });

    // Submit (store & update)
    $('#form-step').on('submit', function (e) {
        e.preventDefault();
        var id = $('#step-id').val();
        var url = id
            ? '{{ route("steps.index") }}/' + id
            : '{{ route("steps.store") }}';

        $('#btn-save').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

        $.ajax({
            url: url,
            type: 'POST',
            data: $(this).serialize(),
            success: function () {
                $('#modal-step').modal('hide');
                table.ajax.reload(null, false);
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Langkah berhasil disimpan.', timer: 2000, showConfirmButton: false });
            },
            error: function (err) {
                if (err.status === 422) {
                    var errors = err.responseJSON.errors;
                    $('.is-invalid').removeClass('is-invalid');
                    Object.keys(errors).forEach(function (key) {
                        $('[name="' + key + '"]').addClass('is-invalid');
                    });
                    Swal.fire({ icon: 'error', title: 'Validasi Gagal', text: 'Mohon periksa kembali inputan Anda.' });
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: 'Terjadi kesalahan pada server.' });
                }
            },
            complete: function () {
                $('#btn-save').prop('disabled', false).html('<i class="mdi mdi-content-save me-1"></i> Simpan');
            },
        });
    });

    // Hapus
    $('#steps-table').on('click', '.btn-delete', function () {
        var id   = $(this).data('id');
        var name = $(this).data('name');

        Swal.fire({
            title: 'Hapus Langkah?',
            html: 'Langkah <strong>' + name + '</strong> akan dihapus secara permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="mdi mdi-trash-can me-1"></i> Ya, Hapus',
            cancelButtonText: 'Batal',
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("steps.index") }}/' + id,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                    success: function () {
                        table.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Langkah berhasil dihapus.', timer: 2000, showConfirmButton: false });
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
