@extends('layouts.admin')

@section('title', 'Galeri')
@section('page-title', 'Galeri')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Galeri</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">Daftar Galeri</h5>
                <button type="button" class="btn btn-primary btn-sm waves-effect btn-add">
                    <i class="mdi mdi-plus me-1"></i> Tambah Foto
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="galleries-table" class="table table-hover align-middle mb-0 dt-responsive nowrap w-100">
                        <thead class="table-light">
                            <tr>
                                <th width="40">#</th>
                                <th width="60">Foto</th>
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
<div class="modal fade" id="modal-gallery" tabindex="-1" aria-labelledby="modal-gallery-title" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-gallery-title">Tambah Foto Galeri</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-gallery" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" id="form-method" value="POST">
                <input type="hidden" name="id" id="gallery-id">
                <div class="modal-body">

                    {{-- Image Upload with Preview --}}
                    <div class="mb-3">
                        <label class="form-label">Foto <span class="text-danger" id="img-required-mark">*</span></label>
                        <div id="image-preview-wrap"
                             class="border rounded d-flex align-items-center justify-content-center bg-light mb-2 overflow-hidden"
                             style="height:180px;cursor:pointer;position:relative;">
                            <img id="image-preview" src="" alt="Preview"
                                 class="img-fluid w-100 h-100" style="object-fit:cover;display:none;">
                            <div id="image-placeholder" class="text-center text-muted">
                                <i class="mdi mdi-image-plus fs-1 d-block"></i>
                                <small>Klik untuk pilih gambar</small>
                            </div>
                        </div>
                        <input type="file" name="image" id="gallery-image" accept="image/jpg,image/jpeg,image/png,image/webp" class="form-control form-control-sm">
                        <small class="text-muted">Format: JPG, PNG, WebP. Maks. 10 MB.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Judul <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="gallery-title" class="form-control"
                               placeholder="Contoh: Wedding di Bali" required maxlength="150">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" id="gallery-description" class="form-control" rows="3"
                                  placeholder="Deskripsi singkat foto ini..." maxlength="1000"></textarea>
                        <small class="text-muted"><span id="desc-count">0</span>/1000 karakter</small>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Urutan Tampil</label>
                            <input type="number" name="sort_order" id="gallery-sort" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-6 d-flex align-items-end pb-1">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="gallery-active" value="1" checked>
                                <label class="form-check-label" for="gallery-active">Aktif</label>
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
    var table = $('#galleries-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("galleries.data") }}',
        columns: [
            { data: 'DT_RowIndex',    name: 'DT_RowIndex',    orderable: false, searchable: false, className: 'text-center' },
            { data: 'image_preview',  name: 'image_path',     orderable: false, searchable: false, className: 'text-center' },
            { data: 'title',          name: 'title' },
            { data: 'description',    name: 'description' },
            { data: 'sort_order',     name: 'sort_order',     className: 'text-center' },
            { data: 'status',         name: 'is_active',      orderable: false, searchable: false, className: 'text-center' },
            { data: 'actions',        name: 'actions',        orderable: false, searchable: false, className: 'text-center' },
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

    // ── Image Preview ────────────────────────────────────────────
    $('#image-preview-wrap').on('click', function () {
        $('#gallery-image').trigger('click');
    });

    $('#gallery-image').on('change', function () {
        var file = this.files[0];
        if (!file) { return; }
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#image-preview').attr('src', e.target.result).show();
            $('#image-placeholder').hide();
        };
        reader.readAsDataURL(file);
    });

    // ── Description count ───────────────────────────────────────
    $('#gallery-description').on('input', function () {
        $('#desc-count').text($(this).val().length);
    });

    // ── Reset form ──────────────────────────────────────────────
    function resetForm() {
        $('#form-gallery')[0].reset();
        $('#form-method').val('POST');
        $('#gallery-id').val('');
        $('#modal-gallery-title').text('Tambah Foto Galeri');
        $('#image-preview').attr('src', '').hide();
        $('#image-placeholder').show();
        $('#img-required-mark').show();
        $('#gallery-image').prop('required', true);
        $('#desc-count').text('0');
        $('.is-invalid').removeClass('is-invalid');
        $('#gallery-active').prop('checked', true);
    }

    // ── Tambah ──────────────────────────────────────────────────
    $('.btn-add').on('click', function () {
        resetForm();
        $('#modal-gallery').modal('show');
    });

    // ── Edit ────────────────────────────────────────────────────
    $('#galleries-table').on('click', '.btn-edit', function () {
        var btn = $(this);
        resetForm();
        $('#modal-gallery-title').text('Edit Foto Galeri');
        $('#form-method').val('PUT');
        $('#gallery-id').val(btn.data('id'));
        $('#gallery-title').val(btn.data('title'));
        $('#gallery-description').val(btn.data('description'));
        $('#desc-count').text(String(btn.data('description') || '').length);
        $('#gallery-sort').val(btn.data('sort_order'));
        $('#gallery-active').prop('checked', btn.data('is_active') == '1');

        // Show existing image
        var imgUrl = btn.data('image_url');
        if (imgUrl) {
            $('#image-preview').attr('src', imgUrl).show();
            $('#image-placeholder').hide();
        }
        // Image not required on edit (can keep existing)
        $('#gallery-image').prop('required', false);
        $('#img-required-mark').hide();

        $('#modal-gallery').modal('show');
    });

    // ── Submit (store & update) ──────────────────────────────────
    $('#form-gallery').on('submit', function (e) {
        e.preventDefault();
        var id = $('#gallery-id').val();
        var url = id
            ? '{{ route("galleries.index") }}/' + id
            : '{{ route("galleries.store") }}';

        $('#btn-save').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

        var formData = new FormData(this);

        // Remove empty file input so nullable image validation doesn't fail
        if (!$('#gallery-image')[0].files.length) {
            formData.delete('image');
        }

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function () {
                $('#modal-gallery').modal('hide');
                table.ajax.reload(null, false);
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Foto galeri berhasil disimpan.', timer: 2000, showConfirmButton: false });
            },
            error: function (err) {
                if (err.status === 422) {
                    var errors = err.responseJSON.errors;
                    $('.is-invalid').removeClass('is-invalid');
                    var messages = [];
                    Object.keys(errors).forEach(function (key) {
                        $('[name="' + key + '"]').addClass('is-invalid');
                        messages.push(errors[key][0]);
                    });
                    Swal.fire({ icon: 'error', title: 'Validasi Gagal', html: messages.join('<br>') });
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: 'Terjadi kesalahan pada server.' });
                }
            },
            complete: function () {
                $('#btn-save').prop('disabled', false).html('<i class="mdi mdi-content-save me-1"></i> Simpan');
            },
        });
    });

    // ── Hapus ────────────────────────────────────────────────────
    $('#galleries-table').on('click', '.btn-delete', function () {
        var id   = $(this).data('id');
        var name = $(this).data('name');

        Swal.fire({
            title: 'Hapus Foto?',
            html: 'Foto <strong>' + name + '</strong> akan dihapus secara permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="mdi mdi-trash-can me-1"></i> Ya, Hapus',
            cancelButtonText: 'Batal',
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("galleries.index") }}/' + id,
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                    success: function () {
                        table.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Foto galeri berhasil dihapus.', timer: 2000, showConfirmButton: false });
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
