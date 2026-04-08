@extends('layouts.admin')

@section('title', 'Pengaturan Pembayaran')
@section('page-title', 'Pengaturan Pembayaran')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Pengaturan Pembayaran</li>
@endsection

@section('content')
<div class="row">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <div class="me-2">
                        <div class="avatar-xs">
                            <span class="avatar-title bg-primary-subtle text-primary rounded-circle font-size-20">
                                <i class="mdi mdi-credit-card-outline"></i>
                            </span>
                        </div>
                    </div>
                    <h5 class="card-title mb-0">Konfigurasi Duitku</h5>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('settings.payment.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="duitku_merchant_code" class="form-label text-dark">Merchant Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('duitku_merchant_code') is-invalid @enderror" 
                               id="duitku_merchant_code" name="duitku_merchant_code" 
                               value="{{ old('duitku_merchant_code', $settings['duitku_merchant_code'] ?? '') }}" 
                               placeholder="Contoh: D1234">
                        @error('duitku_merchant_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Dapatkan Merchant Code dari dashboard Duitku Anda.</small>
                    </div>

                    <div class="mb-3">
                        <label for="duitku_api_key" class="form-label text-dark">API Key <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control @error('duitku_api_key') is-invalid @enderror" 
                                   id="duitku_api_key" name="duitku_api_key" 
                                   value="{{ old('duitku_api_key', $settings['duitku_api_key'] ?? '') }}" 
                                   placeholder="Masukkan API Key Duitku">
                            <button class="btn btn-outline-secondary" type="button" id="toggle-api-key">
                                <i class="mdi mdi-eye-outline text-dark"></i>
                            </button>
                            @error('duitku_api_key')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="text-muted">Gunakan API Key yang sesuai dengan mode (Sandbox/Production).</small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch form-switch-lg">
                            <input class="form-check-input" type="checkbox" id="duitku_is_sandbox" name="duitku_is_sandbox" value="1"
                                {{ old('duitku_is_sandbox', $settings['duitku_is_sandbox'] ?? '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label ms-2 text-dark" for="duitku_is_sandbox">Sandbox Mode</label>
                        </div>
                        <small class="text-muted">Aktifkan untuk testing. Matikan jika sudah siap untuk live/produksi.</small>
                    </div>

                    <div class="mt-4 pt-2">
                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                            <i class="mdi mdi-content-save-outline me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card bg-light border-0 py-3 px-4">
            <div class="card-body">
                <h5 class="card-title text-info mb-4"><i class="mdi mdi-information-outline me-1"></i> Bantuan Pengaturan</h5>
                <p class="card-text text-muted mb-4">
                    Duitku adalah payment gateway Indonesia yang mendukung berbagai metode pembayaran seperti Virtual Account, QRIS, E-Wallet, dan Retail Outlet.
                </p>
                
                <div class="d-flex align-items-start mb-4">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar-xs">
                            <span class="avatar-title bg-info text-white rounded-circle font-size-16">1</span>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1 text-dark">Dapatkan Kredensial</h6>
                        <p class="text-muted mb-0">Daftar atau login ke <a href="https://duitku.com" target="_blank" class="text-info text-decoration-underline">Duitku</a> untuk mendapatkan Merchant Code dan API Key.</p>
                    </div>
                </div>

                <div class="d-flex align-items-start mb-4">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar-xs">
                            <span class="avatar-title bg-info text-white rounded-circle font-size-16">2</span>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1 text-dark">Atur Callback URL</h6>
                        <p class="text-muted mb-0">Salin URL di bawah ini dan tempelkan di Pengaturan Callback pada Dashboard Duitku:</p>
                        <div class="mt-2 position-relative">
                            <code class="d-block p-3 bg-white rounded border text-dark fs-6">{{ url('/api/callback/duitku') }}</code>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar-xs">
                            <span class="avatar-title bg-info text-white rounded-circle font-size-16">3</span>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1 text-dark">Mode Lingkungan</h6>
                        <p class="text-muted mb-0">Selalu gunakan <strong>Sandbox Mode</strong> saat masih dalam tahap pengembangan untuk mensimulasikan pembayaran tanpa biaya asli.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function() {
        $('#toggle-api-key').on('click', function() {
            const input = $('#duitku_api_key');
            const icon = $(this).find('i');
            
            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('mdi-eye-outline').addClass('mdi-eye-off-outline');
            } else {
                input.attr('type', 'password');
                icon.removeClass('mdi-eye-off-outline').addClass('mdi-eye-outline');
            }
        });
    });
</script>
@endpush
