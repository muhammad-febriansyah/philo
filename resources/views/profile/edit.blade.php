@extends('layouts.admin')

@section('title', 'Edit Profil')
@section('page-title', 'Edit Profil')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Edit Profil</li>
@endsection

@push('styles')
<style>
.avatar-upload-area {
    border: 2px dashed #d0d5dd;
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
    cursor: pointer;
    transition: all .25s ease;
    background: #f9fafb;
    position: relative;
}
.avatar-upload-area:hover, .avatar-upload-area.dragover {
    border-color: #556ee6;
    background: #eef1fd;
}
.avatar-upload-area input[type="file"] {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
    width: 100%;
    height: 100%;
}
.avatar-preview-wrap {
    position: relative;
    width: 100px;
    height: 100px;
    margin: 0 auto;
}
.avatar-preview-wrap img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 50%;
    border: 3px solid #556ee6;
}
.avatar-preview-wrap .btn-remove-avatar {
    position: absolute;
    top: -6px;
    right: -6px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    font-size: 11px;
    line-height: 1;
}
.upload-placeholder i { font-size: 2rem; color: #aab3c6; }
.upload-placeholder p { margin: .4rem 0 .2rem; font-weight: 500; color: #495057; font-size: .9rem; }
.upload-placeholder span { font-size: .75rem; color: #9aa1ad; }
</style>
@endpush

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom p-3">
                    <h5 class="card-title mb-0"><i class="mdi mdi-account-edit-outline me-2 text-primary"></i>Informasi Profil</h5>
                    <p class="text-muted small mb-0 mt-1">Perbarui nama, email, foto, dan password akun Anda.</p>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- Avatar --}}
                        <div class="mb-4">
                            <label class="form-label d-block fw-bold">Foto Profil</label>
                            <div class="avatar-upload-area" id="uploadArea">
                                <input type="file" name="avatar" id="avatarInput" accept="image/jpg,image/jpeg,image/png,image/webp">
                                <div class="avatar-preview-wrap" id="previewWrap"
                                    @if(!$user->avatar_path) style="display:none" @endif>
                                    <img src="{{ $user->avatar_path ? Storage::url($user->avatar_path) : '' }}" id="avatarPreview" alt="Avatar">
                                    <button type="button" class="btn btn-danger btn-remove-avatar" id="removeAvatar" title="Hapus foto">
                                        <i class="mdi mdi-close"></i>
                                    </button>
                                </div>
                                <div class="upload-placeholder" id="uploadPlaceholder"
                                    @if($user->avatar_path) style="display:none" @endif>
                                    <i class="mdi mdi-account-circle"></i>
                                    <p>Klik atau seret foto ke sini</p>
                                    <span>JPG, PNG, WebP &mdash; maks. 2 MB</span>
                                </div>
                            </div>
                            <input type="hidden" name="remove_avatar" id="removeAvatarFlag" value="0">
                            @error('avatar')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <hr class="my-4 opacity-25">

                        {{-- Informasi Dasar --}}
                        <h6 class="fw-bold text-muted text-uppercase mb-3" style="font-size:.75rem;letter-spacing:.07em;">Informasi Dasar</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" id="name" name="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $user->name) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Nomor Telepon</label>
                                <input type="text" id="phone" name="phone"
                                    class="form-control @error('phone') is-invalid @enderror"
                                    value="{{ old('phone', $user->phone) }}" placeholder="08xxxxxxxxxx">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Alamat Email <span class="text-danger">*</span></label>
                            <input type="email" id="email" name="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $user->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-control bg-light" value="{{ ucfirst($user->role) }}" readonly>
                            <div class="form-text">Role tidak dapat diubah dari halaman ini.</div>
                        </div>

                        <hr class="my-4 opacity-25">

                        {{-- Ubah Password --}}
                        <h6 class="fw-bold text-muted text-uppercase mb-3" style="font-size:.75rem;letter-spacing:.07em;">Ubah Password</h6>
                        <div class="card border mb-3">
                            <div class="card-header py-2 bg-light">
                                <h6 class="mb-0 text-muted">
                                    <i class="mdi mdi-lock-outline me-1"></i> Password Baru
                                    <small class="fw-normal">(kosongkan jika tidak ingin mengubah)</small>
                                </h6>
                            </div>
                            <div class="card-body pb-2">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="form-label">Password Baru</label>
                                        <div class="input-group">
                                            <input type="password" id="password" name="password"
                                                class="form-control @error('password') is-invalid @enderror"
                                                placeholder="Minimal 8 karakter" autocomplete="new-password">
                                            <button class="btn btn-outline-secondary toggle-pw" type="button" data-target="password">
                                                <i class="mdi mdi-eye-outline"></i>
                                            </button>
                                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                                        <div class="input-group">
                                            <input type="password" id="password_confirmation" name="password_confirmation"
                                                class="form-control" placeholder="Ulangi password baru" autocomplete="new-password">
                                            <button class="btn btn-outline-secondary toggle-pw" type="button" data-target="password_confirmation">
                                                <i class="mdi mdi-eye-outline"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" class="btn btn-primary waves-effect">
                                <i class="mdi mdi-content-save me-1"></i> Simpan Perubahan
                            </button>
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary waves-effect">Batal</a>
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
    var input       = document.getElementById('avatarInput');
    var preview     = document.getElementById('avatarPreview');
    var previewW    = document.getElementById('previewWrap');
    var placeholder = document.getElementById('uploadPlaceholder');
    var removeBtn   = document.getElementById('removeAvatar');
    var removeFlag  = document.getElementById('removeAvatarFlag');
    var area        = document.getElementById('uploadArea');

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

    input.addEventListener('change', function () { if (this.files[0]) showPreview(this.files[0]); });

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
        if (file) { showPreview(file); }
    });

    // Toggle password visibility
    document.querySelectorAll('.toggle-pw').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = document.getElementById(btn.dataset.target);
            var icon   = btn.querySelector('i');
            if (target.type === 'password') {
                target.type = 'text';
                icon.classList.replace('mdi-eye-outline', 'mdi-eye-off-outline');
            } else {
                target.type = 'password';
                icon.classList.replace('mdi-eye-off-outline', 'mdi-eye-outline');
            }
        });
    });
}());
</script>
@endpush
