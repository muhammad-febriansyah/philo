@extends('layouts.admin')

@section('title', 'Edit Pengguna')
@section('page-title', 'Edit Pengguna')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Pengguna</a></li>
    <li class="breadcrumb-item active">Edit</li>
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
    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Form Edit Pengguna</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- Avatar --}}
                        <div class="mb-4">
                            <label class="form-label d-block">Foto Profil</label>
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

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" id="name" name="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $user->name) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" id="email" name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', $user->email) }}" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
                                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="cabang" {{ old('role', $user->role) === 'cabang' ? 'selected' : '' }}>Cabang</option>
                                    <option value="operator" {{ old('role', $user->role) === 'operator' ? 'selected' : '' }}>Operator</option>
                                </select>
                                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Telepon</label>
                                <input type="text" id="phone" name="phone"
                                    class="form-control @error('phone') is-invalid @enderror"
                                    value="{{ old('phone', $user->phone) }}">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="branch_id" class="form-label">Cabang</label>
                            <select id="branch_id" name="branch_id" class="form-select @error('branch_id') is-invalid @enderror">
                                <option value="">-- Semua Cabang (Admin) --</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id', $user->branch_id) == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Wajib diisi untuk role Operator.</div>
                            @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Ubah Password --}}
                        <div class="card border mb-3">
                            <div class="card-header py-2 bg-light">
                                <h6 class="mb-0 text-muted">
                                    <i class="mdi mdi-lock-outline me-1"></i> Ubah Password
                                    <small class="fw-normal">(kosongkan jika tidak ingin mengubah)</small>
                                </h6>
                            </div>
                            <div class="card-body pb-2">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="form-label">Password Baru</label>
                                        <input type="password" id="password" name="password"
                                            class="form-control @error('password') is-invalid @enderror"
                                            placeholder="Minimal 8 karakter">
                                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                                        <input type="password" id="password_confirmation" name="password_confirmation"
                                            class="form-control" placeholder="Ulangi password baru">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-2">
                            <button type="submit" class="btn btn-primary waves-effect">
                                <i class="mdi mdi-content-save me-1"></i> Simpan Perubahan
                            </button>
                            <a href="{{ route('users.index') }}" class="btn btn-secondary waves-effect">Batal</a>
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
    var input    = document.getElementById('avatarInput');
    var preview  = document.getElementById('avatarPreview');
    var previewW = document.getElementById('previewWrap');
    var placeholder = document.getElementById('uploadPlaceholder');
    var removeBtn   = document.getElementById('removeAvatar');
    var removeFlag  = document.getElementById('removeAvatarFlag');
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
        if (file) { input.files = e.dataTransfer.files; showPreview(file); }
    });
})();
</script>
@endpush
