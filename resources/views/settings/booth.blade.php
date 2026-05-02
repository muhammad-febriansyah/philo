@extends('layouts.admin')

@section('title', 'Pengaturan Booth')
@section('page-title', 'Pengaturan Booth')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Pengaturan Booth</li>
@endsection

@section('content')
@include('settings._tabs')
<div class="row">
    <div class="col-xl-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex align-items-center gap-2">
                <span class="d-flex align-items-center justify-content-center rounded-2"
                      style="width:32px;height:32px;background:#fff9e0;border:1.5px solid #C9A800;">
                    <i class="mdi mdi-cash-multiple" style="color:#C9A800;font-size:16px;"></i>
                </span>
                <h5 class="card-title mb-0 fw-semibold">Harga Sesi Booth</h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="mdi mdi-check-circle-outline me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <p class="text-muted small mb-4">
                    Harga ini berlaku untuk semua cabang. Jumlah foto per sesi & ukuran cetak otomatis mengikuti frame yang dipilih pelanggan.
                </p>

                <form action="{{ route('settings.booth.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="booth_base_price" class="form-label text-dark fw-semibold">
                            Harga Sesi <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control fw-bold @error('booth_base_price') is-invalid @enderror"
                               id="booth_base_price"
                               name="booth_base_price"
                               inputmode="numeric"
                               data-role="rupiah"
                               required
                               value="{{ old('booth_base_price', 'Rp '.number_format((int) ($settings['booth_base_price'] ?? 25000), 0, ',', '.')) }}"
                               placeholder="Rp 25.000">
                        @error('booth_base_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Harga 1 sesi foto, sudah termasuk <strong>1 lembar cetak</strong>.</small>
                    </div>

                    <div class="mb-4">
                        <label for="booth_extra_print_price" class="form-label text-dark fw-semibold">
                            Harga Cetak Tambahan <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control fw-bold @error('booth_extra_print_price') is-invalid @enderror"
                               id="booth_extra_print_price"
                               name="booth_extra_print_price"
                               inputmode="numeric"
                               data-role="rupiah"
                               required
                               value="{{ old('booth_extra_print_price', 'Rp '.number_format((int) ($settings['booth_extra_print_price'] ?? 5000), 0, ',', '.')) }}"
                               placeholder="Rp 5.000">
                        @error('booth_extra_print_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Biaya per lembar tambahan di luar 1 lembar default.</small>
                    </div>

                    <div class="mb-4">
                        <label for="booth_max_extra_prints" class="form-label text-dark fw-semibold">
                            Maks. Cetak Tambahan <span class="text-danger">*</span>
                        </label>
                        <input type="number"
                               class="form-control fw-bold @error('booth_max_extra_prints') is-invalid @enderror"
                               id="booth_max_extra_prints"
                               name="booth_max_extra_prints"
                               min="0"
                               max="99"
                               step="1"
                               required
                               value="{{ old('booth_max_extra_prints', $settings['booth_max_extra_prints'] ?? 5) }}"
                               placeholder="5">
                        @error('booth_max_extra_prints')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Batas berapa banyak pelanggan boleh menambah lembar di counter booth.</small>
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
                    <i class="mdi mdi-information-outline text-warning me-1"></i> Cara Kerja Harga Booth
                </h6>
                <ul class="text-muted small mb-0" style="padding-left:18px;line-height:1.7;">
                    <li>Pelanggan tap "Mulai Foto" → counter cetak default <strong>1 lembar</strong>.</li>
                    <li>Pelanggan boleh menambah cetakan sesuai batas maksimum di atas.</li>
                    <li>Total pembayaran = <code>harga sesi + (jumlah tambahan × harga cetak tambahan)</code>.</li>
                    <li>QRIS digenerate sekali dengan nominal final, lalu pelanggan pilih frame & foto.</li>
                </ul>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body">
                <h6 class="fw-semibold text-dark mb-2">
                    <i class="mdi mdi-calculator-variant text-success me-1"></i> Simulasi Harga
                </h6>
                <div id="price-simulator" class="text-muted small">
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span>Sesi (1 lembar)</span>
                        <strong id="sim-base">Rp 0</strong>
                    </div>
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span>+ 2 cetak tambahan</span>
                        <strong id="sim-2">Rp 0</strong>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span>Total maksimum</span>
                        <strong id="sim-max" style="color:#7a6200;">Rp 0</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    function formatRupiah(rawValue) {
        var digits = String(rawValue || '').replace(/\D+/g, '');
        if (!digits) return '';
        return 'Rp ' + digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function parseRupiah(value) {
        var digits = String(value || '').replace(/\D+/g, '');
        return parseInt(digits || '0', 10);
    }

    function fmt(n) { return 'Rp ' + (Number(n) || 0).toLocaleString('id-ID'); }
    function recalc() {
        var base = parseRupiah($('#booth_base_price').val());
        var extra = parseRupiah($('#booth_extra_print_price').val());
        var max = parseInt($('#booth_max_extra_prints').val() || 0, 10);
        $('#sim-base').text(fmt(base));
        $('#sim-2').text(fmt(extra * 2));
        $('#sim-max').text(fmt(base + (extra * max)));
    }

    $('[data-role="rupiah"]').on('input', function () {
        $(this).val(formatRupiah($(this).val()));
    });

    $('#booth_base_price, #booth_extra_print_price, #booth_max_extra_prints').on('input', recalc);
    recalc();
});
</script>
@endpush
