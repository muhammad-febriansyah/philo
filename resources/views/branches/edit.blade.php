@extends('layouts.admin')

@section('title', 'Edit Cabang')
@section('page-title', 'Edit Cabang')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('branches.index') }}">Cabang</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@push('styles')
<style>
.photo-upload-area {
    border: 2px dashed #d0d5dd;
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all .25s ease;
    background: #f9fafb;
    position: relative;
}
.photo-upload-area:hover, .photo-upload-area.dragover {
    border-color: #556ee6;
    background: #eef1fd;
}
.photo-upload-area input[type="file"] {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
    width: 100%;
    height: 100%;
}
.photo-preview-wrap {
    position: relative;
    width: 120px;
    height: 120px;
    margin: 0 auto;
}
.photo-preview-wrap img {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 12px;
    border: 3px solid #556ee6;
}
.photo-preview-wrap .btn-remove-photo {
    position: absolute;
    top: -8px;
    right: -8px;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    font-size: 12px;
    line-height: 1;
}
.upload-placeholder i { font-size: 2.5rem; color: #aab3c6; }
.upload-placeholder p { margin: .5rem 0 .25rem; font-weight: 500; color: #495057; }
.upload-placeholder span { font-size: .8rem; color: #9aa1ad; }
</style>
@endpush

@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Form Edit Cabang</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('branches.update', $branch) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- Foto Cabang --}}
                        <div class="mb-4">
                            <label class="form-label d-block">Foto Cabang</label>
                            <div class="photo-upload-area" id="uploadArea">
                                <input type="file" name="photo" id="photoInput" accept="image/jpg,image/jpeg,image/png,image/webp">
                                <div class="photo-preview-wrap" id="previewWrap"
                                    @if(!$branch->photo) style="display:none" @endif>
                                    <img src="{{ $branch->photo ? Storage::url($branch->photo) : '' }}" id="photoPreview" alt="Foto Cabang">
                                    <button type="button" class="btn btn-danger btn-remove-photo" id="removePhoto" title="Hapus foto">
                                        <i class="mdi mdi-close"></i>
                                    </button>
                                </div>
                                <div class="upload-placeholder" id="uploadPlaceholder"
                                    @if($branch->photo) style="display:none" @endif>
                                    <i class="mdi mdi-image-plus"></i>
                                    <p>Klik atau seret foto ke sini</p>
                                    <span>JPG, PNG, WebP &mdash; maks. 2 MB</span>
                                </div>
                            </div>
                            <input type="hidden" name="remove_photo" id="removePhotoFlag" value="0">
                            @error('photo')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Cabang <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $branch->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="code" class="form-label">Kode Cabang <span class="text-danger">*</span></label>
                            <input type="text" id="code" name="code"
                                class="form-control @error('code') is-invalid @enderror"
                                value="{{ old('code', $branch->code) }}" required>
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Telepon</label>
                            <input type="text" id="phone" name="phone"
                                class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone', $branch->phone) }}">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Alamat</label>
                            <textarea id="address" name="address" rows="3"
                                class="form-control @error('address') is-invalid @enderror">{{ old('address', $branch->address) }}</textarea>
                            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                    value="1" {{ old('is_active', $branch->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Cabang Aktif</label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary waves-effect">
                                <i class="mdi mdi-content-save me-1"></i> Simpan Perubahan
                            </button>
                            <a href="{{ route('branches.index') }}" class="btn btn-secondary waves-effect">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    var input    = document.getElementById('photoInput');
    var preview  = document.getElementById('photoPreview');
    var previewW = document.getElementById('previewWrap');
    var placeholder = document.getElementById('uploadPlaceholder');
    var removeBtn   = document.getElementById('removePhoto');
    var removeFlag  = document.getElementById('removePhotoFlag');
    var area     = document.getElementById('uploadArea');

    function showPreview(file) {
        if (!file || !file.type.startsWith('image/')) return;
        var reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
            previewW.style.display = 'block';
            placeholder.style.display = 'none';
            removeFlag.value = '0';
        };
        reader.readAsDataURL(file);
    }

    input.addEventListener('change', function () {
        if (this.files[0]) showPreview(this.files[0]);
    });

    removeBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        input.value = '';
        preview.src = '';
        previewW.style.display = 'none';
        placeholder.style.display = 'block';
        removeFlag.value = '1';
    });

    area.addEventListener('dragover', function (e) { e.preventDefault(); area.classList.add('dragover'); });
    area.addEventListener('dragleave', function () { area.classList.remove('dragover'); });
    area.addEventListener('drop', function (e) {
        e.preventDefault();
        area.classList.remove('dragover');
        var file = e.dataTransfer.files[0];
        if (file) { input.files = e.dataTransfer.files; showPreview(file); }
    });
})();
</script>
@endpush
