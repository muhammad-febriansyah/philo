@extends('layouts.admin')

@section('title', 'Pengaturan reCAPTCHA')
@section('page-title', 'Pengaturan reCAPTCHA')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">reCAPTCHA</li>
@endsection

@section('content')
@include('settings._tabs')
<div class="row">
    <div class="col-xl-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex align-items-center gap-2">
                <span class="d-flex align-items-center justify-content-center rounded-2"
                      style="width:32px;height:32px;background:#fff9e0;border:1.5px solid #C9A800;">
                    <i class="mdi mdi-shield-check-outline" style="color:#C9A800;font-size:16px;"></i>
                </span>
                <h5 class="card-title mb-0 fw-semibold">Google reCAPTCHA v3</h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="mdi mdi-check-circle-outline me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <p class="text-muted small mb-4">
                    reCAPTCHA v3 melindungi form login & form lain dari spam dan brute-force tanpa mengganggu user.
                    Daftarkan domain di <a href="https://www.google.com/recaptcha/admin/create" target="_blank" rel="noopener">Google reCAPTCHA Admin</a>
                    pilih tipe <strong>v3</strong>, lalu salin <em>Site Key</em> & <em>Secret Key</em> ke form di bawah.
                </p>

                <form action="{{ route('settings.recaptcha.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="border rounded p-3 mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="recaptcha_enabled"
                                   name="recaptcha_enabled" value="1"
                                   {{ old('recaptcha_enabled', $settings['recaptcha_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold text-dark" for="recaptcha_enabled">
                                Aktifkan reCAPTCHA v3
                            </label>
                        </div>
                        <small class="text-muted d-block mt-1">
                            Saat aktif, halaman login akan memvalidasi token reCAPTCHA sebelum auth.
                        </small>
                    </div>

                    <div class="mb-3">
                        <label for="recaptcha_site_key" class="form-label text-dark fw-semibold">
                            Site Key <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control font-monospace @error('recaptcha_site_key') is-invalid @enderror"
                               id="recaptcha_site_key"
                               name="recaptcha_site_key"
                               value="{{ old('recaptcha_site_key', $settings['recaptcha_site_key'] ?? '') }}"
                               placeholder="6Lxxxxxx_xxxxxxxx-xxxxxxxxxxxxxxxxxxxx">
                        @error('recaptcha_site_key')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Diambil dari Google reCAPTCHA Admin Console (sisi public, tampil di HTML).</small>
                    </div>

                    <div class="mb-3">
                        <label for="recaptcha_secret_key" class="form-label text-dark fw-semibold">
                            Secret Key <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control font-monospace @error('recaptcha_secret_key') is-invalid @enderror"
                               id="recaptcha_secret_key"
                               name="recaptcha_secret_key"
                               value="{{ old('recaptcha_secret_key', $settings['recaptcha_secret_key'] ?? '') }}"
                               placeholder="6Lxxxxxx_xxxxxxxx-xxxxxxxxxxxxxxxxxxxx">
                        @error('recaptcha_secret_key')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Wajib dirahasiakan — hanya dipakai server untuk verifikasi token.</small>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn waves-effect"
                                style="background:#C9A800;color:#1a1200;font-weight:600;border:none;">
                            <i class="mdi mdi-content-save me-1"></i> Simpan Pengaturan
                        </button>
                        <a href="{{ route('dashboard') }}" class="btn btn-light">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="card border-0 shadow-sm" style="background:#fffbe6;border:1px dashed #C9A800 !important;">
            <div class="card-body">
                <h6 class="fw-semibold text-dark mb-2">
                    <i class="mdi mdi-information-outline text-warning me-1"></i> Cara Mendapatkan Key
                </h6>
                <ol class="text-muted small mb-0" style="padding-left:18px;line-height:1.7;">
                    <li>Buka <a href="https://www.google.com/recaptcha/admin/create" target="_blank" rel="noopener">Google reCAPTCHA Admin</a>.</li>
                    <li>Pilih tipe <strong>reCAPTCHA v3</strong>.</li>
                    <li>Tambahkan domain (contoh: <code>philo.id</code>, <code>localhost</code> untuk dev).</li>
                    <li>Setujui <em>Terms of Service</em> & klik <strong>Submit</strong>.</li>
                    <li>Salin <em>Site Key</em> & <em>Secret Key</em> ke form di samping.</li>
                </ol>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body">
                <h6 class="fw-semibold text-dark mb-2">
                    <i class="mdi mdi-shield-key-outline text-success me-1"></i> Tempat reCAPTCHA Dipakai
                </h6>
                <ul class="text-muted small mb-0" style="padding-left:18px;line-height:1.7;">
                    <li>Form login admin/operator (<code>/login</code>)</li>
                    <li>Akan otomatis non-aktif jika toggle dimatikan</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
