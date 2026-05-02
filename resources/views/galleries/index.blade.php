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
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 d-flex flex-wrap align-items-center justify-content-between gap-3 py-3">
                <div>
                    <h5 class="card-title mb-1">Koleksi Galeri</h5>
                    <p class="text-muted mb-0">Tampilkan foto galeri dalam format card yang lebih visual.</p>
                </div>
                <button type="button" class="btn btn-primary waves-effect btn-add">
                    <i class="mdi mdi-plus me-1"></i> Tambah Foto
                </button>
            </div>
            <div class="card-body pt-0">
                @if($galleries->isEmpty())
                    <div class="py-5 text-center">
                        <div class="gallery-empty-icon mx-auto mb-3">
                            <i class="mdi mdi-image-plus"></i>
                        </div>
                        <h5 class="mb-2">Belum ada foto galeri</h5>
                        <p class="text-muted mb-4">Tambahkan foto pertama untuk mulai mengisi halaman galeri publik.</p>
                        <button type="button" class="btn btn-primary waves-effect btn-add">
                            <i class="mdi mdi-plus me-1"></i> Tambah Foto
                        </button>
                    </div>
                @else
                    <div class="row g-4" id="gallery-grid">
                        @foreach($galleries as $gallery)
                            <div class="col-md-6 col-xl-4">
                                <div class="gallery-card card h-100 border-0 shadow-sm overflow-hidden">
                                    <div class="gallery-media position-relative">
                                        <img
                                            src="{{ Storage::url($gallery->image_path) }}"
                                            alt="{{ $gallery->title }}"
                                            class="w-100 h-100"
                                            style="object-fit: cover;"
                                        >
                                        <div class="gallery-status">
                                            @if($gallery->is_active)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">Aktif</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Nonaktif</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-body d-flex flex-column">
                                        <div class="d-flex align-items-start justify-content-between gap-3 mb-2">
                                            <h5 class="mb-0">{{ $gallery->title }}</h5>
                                            <span class="gallery-order-badge">#{{ $gallery->sort_order }}</span>
                                        </div>
                                        <p class="text-muted mb-4 flex-grow-1">
                                            {{ $gallery->description ?: 'Belum ada deskripsi untuk foto ini.' }}
                                        </p>
                                        <div class="d-flex align-items-center justify-content-between gap-2 pt-2 border-top">
                                            <small class="text-muted">ID {{ $gallery->id }}</small>
                                            <div class="d-flex gap-2">
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-info waves-effect btn-edit"
                                                    data-id="{{ $gallery->id }}"
                                                    data-title="{{ e($gallery->title) }}"
                                                    data-description="{{ e($gallery->description) }}"
                                                    data-image_url="{{ Storage::url($gallery->image_path) }}"
                                                    data-sort_order="{{ $gallery->sort_order }}"
                                                    data-is_active="{{ $gallery->is_active ? '1' : '0' }}"
                                                >
                                                    <i class="mdi mdi-pencil me-1"></i> Edit
                                                </button>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-danger waves-effect btn-delete"
                                                    data-id="{{ $gallery->id }}"
                                                    data-name="{{ e($gallery->title) }}"
                                                >
                                                    <i class="mdi mdi-trash-can me-1"></i> Hapus
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $galleries->onEachSide(1)->links() }}
                    </div>
                @endif
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

@push('styles')
<style>
    .gallery-card {
        border-radius: 20px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .gallery-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 1rem 2rem rgba(15, 23, 42, 0.08) !important;
    }

    .gallery-media {
        height: 240px;
        background: #f8fafc;
    }

    .gallery-status {
        position: absolute;
        top: 14px;
        right: 14px;
    }

    .gallery-order-badge {
        min-width: 42px;
        padding: 0.35rem 0.65rem;
        border-radius: 999px;
        background: #f8fafc;
        color: #475569;
        font-size: 0.75rem;
        font-weight: 700;
        text-align: center;
    }

    .gallery-empty-icon {
        width: 72px;
        height: 72px;
        border-radius: 22px;
        background: linear-gradient(135deg, #fef9c3 0%, #fde68a 100%);
        color: #b45309;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
    }

    .pagination {
        gap: 0.35rem;
    }

    .pagination .page-link {
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        color: #334155;
        min-width: 40px;
        text-align: center;
    }

    .pagination .page-item.active .page-link {
        background: #18181b;
        border-color: #18181b;
        color: #fff;
    }
</style>
@endpush

@push('scripts')
<script>
$(function () {
    var saveBtnDefaultHtml = $('#btn-save').html();

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

    $('#gallery-description').on('input', function () {
        $('#desc-count').text($(this).val().length);
    });

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

    $('.btn-add').on('click', function () {
        resetForm();
        $('#modal-gallery').modal('show');
    });

    $(document).on('click', '.btn-edit', function () {
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

        var imgUrl = btn.data('image_url');
        if (imgUrl) {
            $('#image-preview').attr('src', imgUrl).show();
            $('#image-placeholder').hide();
        }

        $('#gallery-image').prop('required', false);
        $('#img-required-mark').hide();
        $('#modal-gallery').modal('show');
    });

    $('#form-gallery').on('submit', function (e) {
        e.preventDefault();
        var id = $('#gallery-id').val();
        var url = id
            ? '{{ route("galleries.index") }}/' + id
            : '{{ route("galleries.store") }}';

        $('#btn-save').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

        var formData = new FormData(this);
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
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Foto galeri berhasil disimpan.', timer: 1800, showConfirmButton: false })
                    .then(function () {
                        window.location.reload();
                    });
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
                $('#btn-save').prop('disabled', false).html(saveBtnDefaultHtml);
            },
        });
    });

    $(document).on('click', '.btn-delete', function () {
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
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Foto galeri berhasil dihapus.', timer: 1800, showConfirmButton: false })
                            .then(function () {
                                window.location.reload();
                            });
                    },
                    error: function () {
                        Swal.fire({ icon: 'error', title: 'Gagal!', text: 'Foto galeri gagal dihapus.' });
                    }
                });
            }
        });
    });
});
</script>
@endpush
