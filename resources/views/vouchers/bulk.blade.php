@extends('layouts.admin')

@section('title', 'Bulk Generate Voucher')
@section('page-title', 'Bulk Generate Voucher')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('vouchers.index') }}">Voucher</a></li>
    <li class="breadcrumb-item active">Bulk Generate</li>
@endsection

@section('content')
<form method="POST" action="{{ route('vouchers.bulk.store') }}">
    @csrf
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-box-open me-1 text-warning"></i> Setelan Bulk</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Jumlah Voucher <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" min="1" max="500" value="{{ old('quantity', 50) }}" required
                                   class="form-control @error('quantity') is-invalid @enderror">
                            <small class="text-muted">Maksimum 500 sekali generate.</small>
                            @error('quantity') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Prefix Kode</label>
                            <input type="text" name="prefix" value="{{ old('prefix', $suggestedPrefix) }}" maxlength="16"
                                   class="form-control text-uppercase @error('prefix') is-invalid @enderror"
                                   placeholder="contoh: PROMO-">
                            <small class="text-muted">Kode = prefix + 6 karakter acak.</small>
                            @error('prefix') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Maks. Pemakaian per Voucher</label>
                            <input type="number" name="max_uses" min="1" value="{{ old('max_uses', 1) }}"
                                   class="form-control @error('max_uses') is-invalid @enderror">
                            <small class="text-muted">Default 1 (sekali pakai).</small>
                            @error('max_uses') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama (opsional)</label>
                            <input type="text" name="name" value="{{ old('name') }}" maxlength="255"
                                   class="form-control @error('name') is-invalid @enderror"
                                   placeholder="contoh: Promo Lebaran 2026">
                            @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Tipe Diskon <span class="text-danger">*</span></label>
                            <select name="type" id="voucher-type" class="form-select @error('type') is-invalid @enderror">
                                <option value="percentage" @selected(old('type', 'percentage') === 'percentage')>Persentase</option>
                                <option value="fixed" @selected(old('type') === 'fixed')>Nominal</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Nilai <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text" id="value-prefix">{{ old('type', 'percentage') === 'percentage' ? '%' : 'Rp' }}</span>
                                <input type="number" name="value" min="0" step="0.01" value="{{ old('value', 10) }}" required
                                       class="form-control @error('value') is-invalid @enderror">
                            </div>
                            @error('value') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Min. Pembelian</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="min_purchase" min="0" step="1000" value="{{ old('min_purchase') }}"
                                       class="form-control @error('min_purchase') is-invalid @enderror" placeholder="opsional">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Berlaku Dari</label>
                            <input type="datetime-local" name="valid_from" value="{{ old('valid_from') }}"
                                   class="form-control @error('valid_from') is-invalid @enderror">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Berlaku Sampai</label>
                            <input type="datetime-local" name="valid_until" value="{{ old('valid_until') }}"
                                   class="form-control @error('valid_until') is-invalid @enderror">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-bottom">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-box me-1 text-warning"></i> Batasan Paket</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">Kosongkan = berlaku untuk semua paket.</p>
                    <div class="row g-2" style="max-height:240px;overflow:auto;">
                        @foreach($packages as $pkg)
                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" name="applicable_packages[]" value="{{ $pkg->id }}"
                                           id="bulk-pkg-{{ $pkg->id }}" class="form-check-input"
                                           @checked(in_array($pkg->id, old('applicable_packages', [])))>
                                    <label for="bulk-pkg-{{ $pkg->id }}" class="form-check-label">{{ $pkg->name }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-store me-1 text-warning"></i> Batasan Cabang</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">Kosongkan = berlaku untuk semua cabang.</p>
                    <div class="row g-2" style="max-height:240px;overflow:auto;">
                        @foreach($branches as $br)
                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" name="applicable_branches[]" value="{{ $br->id }}"
                                           id="bulk-br-{{ $br->id }}" class="form-check-input"
                                           @checked(in_array($br->id, old('applicable_branches', [])))>
                                    <label for="bulk-br-{{ $br->id }}" class="form-check-label">{{ $br->name }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="{{ route('vouchers.index') }}" class="btn btn-light">Batal</a>
        <button type="submit" class="btn waves-effect" style="background:#C9A800;color:#1a1200;font-weight:600;border:none;">
            <i class="fas fa-box-open me-1"></i> Generate Voucher
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
$(function () {
    function syncTypeUI() {
        var t = $('#voucher-type').val();
        $('#value-prefix').text(t === 'percentage' ? '%' : 'Rp');
    }
    $('#voucher-type').on('change', syncTypeUI);
});
</script>
@endpush
