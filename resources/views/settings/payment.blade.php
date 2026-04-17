@extends('layouts.admin')

@section('title', 'Pengaturan Pembayaran')
@section('page-title', 'Pengaturan Pembayaran')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Pengaturan Pembayaran</li>
@endsection

@section('content')
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

                <form action="{{ route('settings.payment.update') }}" method="POST">
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
                    <code class="d-block p-2 bg-light rounded border text-dark small" id="callback-url">{{ route('booth.payment.callback') }}</code>
                    <button class="btn btn-sm btn-outline-secondary position-absolute top-50 end-0 translate-middle-y me-1"
                            onclick="copyCallbackUrl()" title="Salin URL" type="button">
                        <i class="mdi mdi-content-copy"></i>
                    </button>
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
        }

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
