@extends('layouts.admin')

@section('title', 'Pengaturan Email')
@section('page-title', 'Pengaturan Email')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Pengaturan Email</li>
@endsection

@section('content')
@include('settings._tabs')
<div class="row">
    <div class="col-xl-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex align-items-center gap-2">
                <span class="d-flex align-items-center justify-content-center rounded-2"
                      style="width:32px;height:32px;background:#fff9e0;border:1.5px solid #C9A800;">
                    <i class="mdi mdi-email-send-outline" style="color:#C9A800;font-size:16px;"></i>
                </span>
                <h5 class="card-title mb-0 fw-semibold">Notifikasi Email (Mailketing)</h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="mdi mdi-check-circle-outline me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <p class="text-muted small mb-4">
                    Sistem mengirim email transaksi & sesi foto via <a href="https://mailketing.co.id" target="_blank" rel="noopener">Mailketing</a>.
                    Pastikan domain pengirim sudah diverifikasi dan saldo email tersedia di akun Mailketing.
                </p>

                <form action="{{ route('settings.email.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Master toggle --}}
                    <div class="border rounded p-3 mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="email_notifications_enabled"
                                   name="email_notifications_enabled" value="1"
                                   {{ old('email_notifications_enabled', $settings['email_notifications_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold text-dark" for="email_notifications_enabled">
                                Aktifkan Notifikasi Email
                            </label>
                        </div>
                        <small class="text-muted d-block mt-1">
                            Saat aktif, sistem akan mengirim email otomatis sesuai event yang dipilih di bawah.
                        </small>
                    </div>

                    {{-- Mailketing config --}}
                    <h6 class="text-uppercase text-muted fw-semibold small mb-2 mt-3" style="letter-spacing:0.05em;">Kredensial Mailketing</h6>

                    <div class="mb-3">
                        <label for="mailketing_api_token" class="form-label text-dark fw-semibold">
                            API Token <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control font-monospace @error('mailketing_api_token') is-invalid @enderror"
                               id="mailketing_api_token"
                               name="mailketing_api_token"
                               value="{{ old('mailketing_api_token', $settings['mailketing_api_token'] ?? '') }}"
                               placeholder="contoh: fa8bd42078ed4xxxxxxxxxxxxxxxxxxx">
                        @error('mailketing_api_token')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Ambil dari menu <em>Integration → API Token</em> di dashboard Mailketing.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mailketing_from_name" class="form-label text-dark fw-semibold">
                                    Nama Pengirim <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control @error('mailketing_from_name') is-invalid @enderror"
                                       id="mailketing_from_name"
                                       name="mailketing_from_name"
                                       value="{{ old('mailketing_from_name', $settings['mailketing_from_name'] ?? config('app.name')) }}"
                                       placeholder="Philo Photobooth">
                                @error('mailketing_from_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mailketing_from_email" class="form-label text-dark fw-semibold">
                                    Email Pengirim <span class="text-danger">*</span>
                                </label>
                                <input type="email"
                                       class="form-control @error('mailketing_from_email') is-invalid @enderror"
                                       id="mailketing_from_email"
                                       name="mailketing_from_email"
                                       value="{{ old('mailketing_from_email', $settings['mailketing_from_email'] ?? '') }}"
                                       placeholder="noreply@domain.com">
                                @error('mailketing_from_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Domain harus sudah diverifikasi di Mailketing.</small>
                            </div>
                        </div>
                    </div>

                    {{-- Triggers --}}
                    <h6 class="text-uppercase text-muted fw-semibold small mb-2 mt-3" style="letter-spacing:0.05em;">Event Notifikasi</h6>

                    <div class="border rounded p-3 mb-3">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="email_notify_paid"
                                   name="email_notify_paid" value="1"
                                   {{ old('email_notify_paid', $settings['email_notify_paid'] ?? '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="email_notify_paid">
                                <span class="fw-semibold text-dark">Pembayaran Berhasil</span>
                                <small class="d-block text-muted">Kirim resi/struk transaksi ke pelanggan setelah QRIS dibayar.</small>
                            </label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="email_notify_session_complete"
                                   name="email_notify_session_complete" value="1"
                                   {{ old('email_notify_session_complete', $settings['email_notify_session_complete'] ?? '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="email_notify_session_complete">
                                <span class="fw-semibold text-dark">Sesi Foto Selesai</span>
                                <small class="d-block text-muted">Kirim link unduh hasil foto saat pelanggan mengisi email di akhir sesi.</small>
                            </label>
                        </div>
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

    {{-- Test Send card --}}
    <div class="col-xl-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex align-items-center gap-2">
                <span class="d-flex align-items-center justify-content-center rounded-2"
                      style="width:32px;height:32px;background:#e0f7e0;border:1.5px solid #198754;">
                    <i class="mdi mdi-send-check-outline text-success" style="font-size:16px;"></i>
                </span>
                <h5 class="card-title mb-0 fw-semibold">Test Kirim Email</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Pastikan konfigurasi disimpan dulu, lalu kirim email uji ke alamat di bawah untuk verifikasi.
                </p>
                <div class="mb-3">
                    <label for="test-recipient" class="form-label text-dark fw-semibold">Email Penerima</label>
                    <input type="email" id="test-recipient" class="form-control"
                           value="{{ auth()->user()->email }}"
                           placeholder="email@domain.com">
                </div>
                <button type="button" id="btn-test-send" class="btn btn-success w-100">
                    <i class="mdi mdi-rocket me-1"></i> Kirim Email Uji
                </button>
                <div id="test-result" class="alert alert-light border mt-3 d-none small mb-0"></div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3" style="background:#fffbe6;border:1px dashed #C9A800 !important;">
            <div class="card-body">
                <h6 class="fw-semibold text-dark mb-2">
                    <i class="mdi mdi-information-outline text-warning me-1"></i> Tips
                </h6>
                <ul class="text-muted small mb-0" style="padding-left:18px;line-height:1.7;">
                    <li>Domain pengirim wajib diverifikasi di Mailketing (DNS DKIM/SPF).</li>
                    <li>Kalau API token salah, response akan menampilkan pesan error spesifik.</li>
                    <li>Email transaksi memakai HTML template — pelanggan akan menerima struk yang rapi.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    $('#btn-test-send').on('click', function () {
        var $btn = $(this);
        var $result = $('#test-result');
        var recipient = $('#test-recipient').val().trim();

        if (! recipient) {
            $result.removeClass('d-none alert-success alert-danger').addClass('alert-warning')
                .html('<i class="mdi mdi-alert-outline me-1"></i> Email penerima wajib diisi.');
            return;
        }

        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Mengirim...');
        $result.addClass('d-none');

        $.ajax({
            url: '{{ route("settings.email.test") }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', recipient: recipient },
        }).done(function (res) {
            $result.removeClass('d-none alert-warning alert-danger').addClass('alert-success')
                .html('<i class="mdi mdi-check-circle-outline me-1"></i> ' + (res.message || 'Email berhasil dikirim.'));
        }).fail(function (xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Gagal mengirim email uji.';
            $result.removeClass('d-none alert-warning alert-success').addClass('alert-danger')
                .html('<i class="mdi mdi-close-circle-outline me-1"></i> ' + msg);
        }).always(function () {
            $btn.prop('disabled', false).html('<i class="mdi mdi-rocket me-1"></i> Kirim Email Uji');
        });
    });
});
</script>
@endpush
