@extends('layouts.admin')

@section('title', 'Pengaturan Pembayaran')
@section('page-title', 'Pengaturan Pembayaran')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Pengaturan Pembayaran</li>
@endsection

@section('content')
@include('settings._tabs')
<div class="row">
    <div class="col-xl-7">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Konfigurasi Payment Gateway</h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="mdi mdi-check-circle-outline me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @php
                    $activeProvider = old('payment_provider', $settings['payment_provider'] ?? 'doku');
                @endphp

                <form action="{{ route('settings.payment.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="form-label text-dark fw-semibold d-block mb-2">Provider Aktif <span class="text-danger">*</span></label>
                        <div class="d-flex gap-3 flex-wrap">
                            <div class="form-check form-check-inline border rounded px-3 py-2">
                                <input class="form-check-input provider-radio" type="radio" name="payment_provider" id="provider_doku" value="doku"
                                       {{ $activeProvider === 'doku' ? 'checked' : '' }}>
                                <label class="form-check-label text-dark fw-semibold" for="provider_doku">DOKU</label>
                            </div>
                            <div class="form-check form-check-inline border rounded px-3 py-2">
                                <input class="form-check-input provider-radio" type="radio" name="payment_provider" id="provider_duitku" value="duitku"
                                       {{ $activeProvider === 'duitku' ? 'checked' : '' }}>
                                <label class="form-check-label text-dark fw-semibold" for="provider_duitku">Duitku</label>
                            </div>
                            <div class="form-check form-check-inline border rounded px-3 py-2">
                                <input class="form-check-input provider-radio" type="radio" name="payment_provider" id="provider_manual" value="manual"
                                       {{ $activeProvider === 'manual' ? 'checked' : '' }}>
                                <label class="form-check-label text-dark fw-semibold" for="provider_manual">Manual QRIS</label>
                            </div>
                        </div>
                        @error('payment_provider')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Pilih provider yang akan dipakai sistem saat membuat transaksi QRIS.</small>
                    </div>

                    <div id="doku-config" class="border rounded p-3 mb-4 provider-section">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <img src="https://www.doku.com/favicon.ico" alt="DOKU" width="20" height="20" onerror="this.style.display='none'">
                            <h6 class="mb-0 text-dark">Konfigurasi DOKU</h6>
                            <span class="badge bg-primary-subtle text-primary">DOKU Snap API</span>
                        </div>

                        <div class="mb-3">
                            <label for="doku_client_id" class="form-label text-dark fw-semibold">Client ID</label>
                            <input type="text"
                                   class="form-control @error('doku_client_id') is-invalid @enderror"
                                   id="doku_client_id"
                                   name="doku_client_id"
                                   value="{{ old('doku_client_id', $settings['doku_client_id'] ?? '') }}"
                                   placeholder="Contoh: MCH-0001-12345678901234">
                            @error('doku_client_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="doku_secret_key" class="form-label text-dark fw-semibold">Secret Key</label>
                            <div class="input-group">
                                <input type="password"
                                       class="form-control @error('doku_secret_key') is-invalid @enderror"
                                       id="doku_secret_key"
                                       name="doku_secret_key"
                                       value="{{ old('doku_secret_key', $settings['doku_secret_key'] ?? '') }}"
                                       placeholder="Masukkan Secret Key DOKU">
                                <button class="btn btn-outline-secondary" type="button" id="toggle-doku-secret-key">
                                    <i class="mdi mdi-eye-outline text-dark"></i>
                                </button>
                                @error('doku_secret_key')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="doku_merchant_id" class="form-label text-dark fw-semibold">
                                    Merchant ID <span class="text-muted small fw-normal">(opsional)</span>
                                </label>
                                <input type="text"
                                       class="form-control @error('doku_merchant_id') is-invalid @enderror"
                                       id="doku_merchant_id"
                                       name="doku_merchant_id"
                                       value="{{ old('doku_merchant_id', $settings['doku_merchant_id'] ?? '') }}"
                                       placeholder="contoh: BRN-0221-1770643536857">
                                @error('doku_merchant_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Brand/Merchant ID dari DOKU. Kosong = pakai Client ID.</small>
                            </div>
                            <div class="col-md-6">
                                <label for="doku_terminal_id" class="form-label text-dark fw-semibold">
                                    Terminal ID <span class="text-muted small fw-normal">(opsional)</span>
                                </label>
                                <input type="text"
                                       class="form-control @error('doku_terminal_id') is-invalid @enderror"
                                       id="doku_terminal_id"
                                       name="doku_terminal_id"
                                       value="{{ old('doku_terminal_id', $settings['doku_terminal_id'] ?? '') }}"
                                       placeholder="contoh: BOOTH001">
                                @error('doku_terminal_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Terminal ID QRIS dari DOKU. Kosong = default <code>BOOTH001</code>.</small>
                            </div>
                        </div>

                        <div class="form-check form-switch form-switch-lg">
                            <input class="form-check-input" type="checkbox"
                                   id="doku_is_sandbox" name="doku_is_sandbox" value="1"
                                   {{ old('doku_is_sandbox', $settings['doku_is_sandbox'] ?? '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label ms-2 text-dark" for="doku_is_sandbox">Sandbox Mode</label>
                        </div>
                    </div>

                    <div id="duitku-config" class="border rounded p-3 mb-4 provider-section">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <h6 class="mb-0 text-dark">Konfigurasi Duitku</h6>
                            <span class="badge bg-success-subtle text-success">Duitku API</span>
                        </div>

                        <div class="mb-3">
                            <label for="duitku_merchant_code" class="form-label text-dark fw-semibold">Merchant Code</label>
                            <input type="text"
                                   class="form-control @error('duitku_merchant_code') is-invalid @enderror"
                                   id="duitku_merchant_code"
                                   name="duitku_merchant_code"
                                   value="{{ old('duitku_merchant_code', $settings['duitku_merchant_code'] ?? '') }}"
                                   placeholder="Masukkan Merchant Code Duitku">
                            @error('duitku_merchant_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="duitku_api_key" class="form-label text-dark fw-semibold">API Key</label>
                            <div class="input-group">
                                <input type="password"
                                       class="form-control @error('duitku_api_key') is-invalid @enderror"
                                       id="duitku_api_key"
                                       name="duitku_api_key"
                                       value="{{ old('duitku_api_key', $settings['duitku_api_key'] ?? '') }}"
                                       placeholder="Masukkan API Key Duitku">
                                <button class="btn btn-outline-secondary" type="button" id="toggle-duitku-api-key">
                                    <i class="mdi mdi-eye-outline text-dark"></i>
                                </button>
                                @error('duitku_api_key')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="duitku_payment_method" class="form-label text-dark fw-semibold">Payment Method Code</label>
                            <input type="text"
                                   class="form-control @error('duitku_payment_method') is-invalid @enderror"
                                   id="duitku_payment_method"
                                   name="duitku_payment_method"
                                   value="{{ old('duitku_payment_method', $settings['duitku_payment_method'] ?? 'GQ') }}"
                                   placeholder="Contoh: GQ">
                            @error('duitku_payment_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                Default <strong>GQ</strong> (QRIS). Isi kode metode lain sesuai dokumentasi Duitku jika ingin non-QR.
                            </small>
                        </div>

                        <div class="form-check form-switch form-switch-lg">
                            <input class="form-check-input" type="checkbox"
                                   id="duitku_is_sandbox" name="duitku_is_sandbox" value="1"
                                   {{ old('duitku_is_sandbox', $settings['duitku_is_sandbox'] ?? '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label ms-2 text-dark" for="duitku_is_sandbox">Sandbox Mode</label>
                        </div>
                    </div>

                    <div id="manual-config" class="border rounded p-3 mb-4 provider-section">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <h6 class="mb-0 text-dark">Konfigurasi Manual QRIS</h6>
                            <span class="badge bg-warning-subtle text-warning">Tanpa Gateway</span>
                        </div>
                        <p class="text-muted small mb-3">
                            Upload gambar QRIS statis kamu. Pelanggan akan scan gambar ini lalu kasir mengkonfirmasi pembayaran secara manual di layar booth.
                        </p>

                        @if($manualQrisImageUrl)
                            <div class="mb-3">
                                <label class="form-label text-dark fw-semibold">QRIS Saat Ini</label>
                                <div class="d-flex align-items-start gap-3">
                                    <img src="{{ $manualQrisImageUrl }}" alt="QRIS" class="rounded border" style="max-width: 180px; max-height: 180px; object-fit: contain;">
                                    <div class="text-muted small">
                                        <p class="mb-1">Gambar ini ditampilkan di booth saat metode Manual QRIS aktif.</p>
                                        <p class="mb-0">Upload gambar baru di bawah untuk menggantinya.</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="manual_qris_image" class="form-label text-dark fw-semibold">
                                {{ $manualQrisImageUrl ? 'Ganti Gambar QRIS' : 'Upload Gambar QRIS' }}
                                @if(!$manualQrisImageUrl) <span class="text-danger">*</span> @endif
                            </label>
                            <input type="file"
                                   class="form-control @error('manual_qris_image') is-invalid @enderror"
                                   id="manual_qris_image"
                                   name="manual_qris_image"
                                   accept="image/jpeg,image/png">
                            @error('manual_qris_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Format JPG/PNG, maksimal 2MB. Gunakan gambar QRIS resmi dari bank/e-wallet kamu.</small>
                        </div>

                        <div id="qris-preview-wrapper" class="mb-3" style="display:none;">
                            <label class="form-label text-dark fw-semibold">Preview</label>
                            <img id="qris-preview" src="" alt="Preview QRIS" class="d-block rounded border" style="max-width: 200px; max-height: 200px; object-fit: contain;">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary waves-effect waves-light">
                        <i class="mdi mdi-content-save-outline me-1"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title text-info mb-3">
                    <i class="mdi mdi-information-outline me-1"></i> Callback URL
                </h5>
                <p class="text-muted mb-2">Daftarkan URL ini di dashboard provider yang aktif:</p>
                <div class="position-relative">
                    <code class="d-block p-2 bg-light rounded border text-dark small" id="callback-url">{{ route('api.booth.payment.callback') }}</code>
                    <button class="btn btn-sm btn-outline-secondary position-absolute top-50 end-0 translate-middle-y me-1"
                            onclick="copyCallbackUrl()" title="Salin URL" type="button">
                        <i class="mdi mdi-content-copy"></i>
                    </button>
                </div>
                <div class="mt-3 p-2 rounded" style="background:#fff7e0;border:1px dashed #C9A800;">
                    <p class="small mb-1 fw-semibold text-dark">
                        <i class="mdi mdi-alert-circle-outline text-warning me-1"></i> DOKU SNAP API
                    </p>
                    <p class="small text-muted mb-0">
                        Halaman <em>QRIS Notify URL</em> di DOKU dashboard hanya untuk Checkout API (legacy). Untuk SNAP API, daftarkan URL ini lewat menu <strong>Integrations</strong> atau hubungi support DOKU.
                    </p>
                </div>
            </div>
        </div>

        <div class="card bg-light border-0">
            <div class="card-body">
                <h6 class="card-title text-dark mb-3"><i class="mdi mdi-api me-1"></i>Endpoint Ringkas</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-borderless mb-0 text-muted small">
                        <tbody>
                            <tr>
                                <td class="fw-semibold text-dark pe-3">DOKU Sandbox</td>
                                <td><code>https://api-sandbox.doku.com</code></td>
                            </tr>
                            <tr>
                                <td class="fw-semibold text-dark pe-3">DOKU Production</td>
                                <td><code>https://api.doku.com</code></td>
                            </tr>
                            <tr>
                                <td class="fw-semibold text-dark pe-3">Duitku Sandbox</td>
                                <td><code>https://sandbox.duitku.com/webapi</code></td>
                            </tr>
                            <tr>
                                <td class="fw-semibold text-dark pe-3">Duitku Production</td>
                                <td><code>https://passport.duitku.com/webapi</code></td>
                            </tr>
                        </tbody>
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
        function toggleProviderSection() {
            const provider = $('input[name="payment_provider"]:checked').val() || 'doku';
            $('#doku-config').toggle(provider === 'doku');
            $('#duitku-config').toggle(provider === 'duitku');
            $('#manual-config').toggle(provider === 'manual');
        }

        $('#manual_qris_image').on('change', function () {
            const file = this.files[0];
            if (!file) { $('#qris-preview-wrapper').hide(); return; }
            const reader = new FileReader();
            reader.onload = (e) => {
                $('#qris-preview').attr('src', e.target.result);
                $('#qris-preview-wrapper').show();
            };
            reader.readAsDataURL(file);
        });

        function toggleInputVisibility(buttonSelector, inputSelector) {
            $(buttonSelector).on('click', function () {
                const input = $(inputSelector);
                const icon = $(this).find('i');
                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('mdi-eye-outline').addClass('mdi-eye-off-outline');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('mdi-eye-off-outline').addClass('mdi-eye-outline');
                }
            });
        }

        $('.provider-radio').on('change', toggleProviderSection);
        toggleProviderSection();

        toggleInputVisibility('#toggle-doku-secret-key', '#doku_secret_key');
        toggleInputVisibility('#toggle-duitku-api-key', '#duitku_api_key');
    });

    function copyCallbackUrl() {
        const url = document.getElementById('callback-url').innerText;
        navigator.clipboard.writeText(url).then(() => {
            toastr.success('URL berhasil disalin!');
        });
    }
</script>
@endpush
