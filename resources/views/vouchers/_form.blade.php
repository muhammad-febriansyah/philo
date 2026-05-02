@php
    $isEdit = isset($voucher) && $voucher;
    $defaultCode = old('code', $isEdit ? $voucher->code : ($suggestedCode ?? ''));
    $defaultType = old('type', $isEdit ? $voucher->type : 'percentage');
    $defaultValue = old('value', $isEdit ? rtrim(rtrim(number_format((float) $voucher->value, 2, '.', ''), '0'), '.') : 10);
    $defaultValueDisplay = $defaultType === 'fixed' && $defaultValue !== ''
        ? 'Rp '.number_format((float) $defaultValue, 0, ',', '.')
        : $defaultValue;
    $defaultMinPurchase = old('min_purchase', $isEdit ? ($voucher->min_purchase ? (int) $voucher->min_purchase : '') : '');
    $defaultMinPurchaseDisplay = $defaultMinPurchase !== ''
        ? 'Rp '.number_format((float) $defaultMinPurchase, 0, ',', '.')
        : '';
    $selectedPackages = old('applicable_packages', $isEdit ? ($voucher->applicable_packages ?? []) : []);
    $selectedBranches = old('applicable_branches', $isEdit ? ($voucher->applicable_branches ?? []) : []);
    $defaultValidFrom = old('valid_from', $isEdit && $voucher->valid_from ? $voucher->valid_from->format('d/m/Y') : '');
    $defaultValidUntil = old('valid_until', $isEdit && $voucher->valid_until ? $voucher->valid_until->format('d/m/Y') : '');
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}">
@endpush

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-bottom">
                <h6 class="mb-0 fw-bold"><i class="fas fa-tag me-1 text-warning"></i> Informasi Voucher</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Kode Voucher <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" name="code" value="{{ $defaultCode }}" required
                                   class="form-control text-uppercase fw-bold @error('code') is-invalid @enderror"
                                   maxlength="64" style="letter-spacing:.06em;">
                            @if(! $isEdit)
                                <button type="button" class="btn btn-outline-secondary" id="btn-regen-code" title="Generate ulang">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            @endif
                        </div>
                        <small class="text-muted">Huruf besar, angka, _ atau -. Contoh: A8K2M9QX</small>
                        @error('code') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nama (opsional)</label>
                        <input type="text" name="name" value="{{ old('name', $isEdit ? $voucher->name : '') }}"
                               class="form-control @error('name') is-invalid @enderror" maxlength="255"
                               placeholder="Contoh: Promo Lebaran 2026">
                        @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tipe Diskon <span class="text-danger">*</span></label>
                        <select name="type" id="voucher-type" class="form-select @error('type') is-invalid @enderror">
                            <option value="percentage" @selected($defaultType === 'percentage')>Persentase (%)</option>
                            <option value="fixed" @selected($defaultType === 'fixed')>Nominal (Rp)</option>
                        </select>
                        @error('type') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold" id="value-label">Nilai Diskon <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text" id="value-prefix">{{ $defaultType === 'percentage' ? '%' : 'Rp' }}</span>
                            <input type="text" name="value" value="{{ $defaultValueDisplay }}" required
                                   inputmode="{{ $defaultType === 'percentage' ? 'decimal' : 'numeric' }}"
                                   data-role="discount-value"
                                   class="form-control @error('value') is-invalid @enderror">
                        </div>
                        @error('value') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Min. Pembelian</label>
                        <input type="text" name="min_purchase"
                               value="{{ $defaultMinPurchaseDisplay }}"
                               inputmode="numeric"
                               data-role="money"
                               class="form-control @error('min_purchase') is-invalid @enderror"
                               placeholder="Rp 0">
                        @error('min_purchase') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Maks. Pemakaian</label>
                        <input type="number" name="max_uses" min="1" step="1"
                               value="{{ old('max_uses', $isEdit ? $voucher->max_uses : '') }}"
                               class="form-control @error('max_uses') is-invalid @enderror"
                               placeholder="kosong = tak terbatas">
                        @error('max_uses') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Berlaku Dari</label>
                        <input type="text" name="valid_from"
                               value="{{ $defaultValidFrom }}"
                               autocomplete="off"
                               data-role="datepicker"
                               class="form-control @error('valid_from') is-invalid @enderror">
                        @error('valid_from') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Berlaku Sampai</label>
                        <input type="text" name="valid_until"
                               value="{{ $defaultValidUntil }}"
                               autocomplete="off"
                               data-role="datepicker"
                               class="form-control @error('valid_until') is-invalid @enderror">
                        @error('valid_until') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" id="is_active" class="form-check-input"
                                @checked(old('is_active', $isEdit ? $voucher->is_active : true))>
                            <label for="is_active" class="form-check-label fw-semibold">Aktifkan voucher</label>
                            <div class="text-muted small">Voucher harus aktif agar bisa digunakan oleh pelanggan.</div>
                        </div>
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
                                       id="pkg-{{ $pkg->id }}" class="form-check-input"
                                       @checked(in_array($pkg->id, $selectedPackages))>
                                <label for="pkg-{{ $pkg->id }}" class="form-check-label">
                                    <span class="fw-semibold">{{ $pkg->name }}</span>
                                    <span class="text-muted small ms-1">— Rp {{ number_format($pkg->price, 0, ',', '.') }}</span>
                                </label>
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
                                       id="br-{{ $br->id }}" class="form-check-input"
                                       @checked(in_array($br->id, $selectedBranches))>
                                <label for="br-{{ $br->id }}" class="form-check-label">
                                    <span class="fw-semibold">{{ $br->name }}</span>
                                    <span class="text-muted small ms-1">— {{ $br->code }}</span>
                                </label>
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
        <i class="fas fa-save me-1"></i> {{ $isEdit ? 'Simpan Perubahan' : 'Buat Voucher' }}
    </button>
</div>

@push('scripts')
<script src="{{ asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('assets/libs/bootstrap-datepicker/locales/bootstrap-datepicker.id.min.js') }}"></script>
<script>
$(function () {
    var $form = $('#voucher-form');
    var $submitButton = $form.find('button[type=submit]');

    function formatRupiah(rawValue) {
        var digits = String(rawValue || '').replace(/\D+/g, '');
        if (!digits) return '';
        return 'Rp ' + digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function clearFieldErrors() {
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('[data-error-for]').remove();
    }

    function renderFieldErrors(errors) {
        clearFieldErrors();

        Object.keys(errors || {}).forEach(function (field) {
            var $field = $form.find('[name="' + field + '"]');

            if (!$field.length && field.indexOf('.') !== -1) {
                $field = $form.find('[name="' + field.replace(/\.\d+/g, '[]') + '"]');
            }

            if (!$field.length) return;

            $field.addClass('is-invalid');

            var message = errors[field][0];
            var $container = $('<div/>', {
                class: 'text-danger small mt-1',
                'data-error-for': field,
                text: message
            });

            var $anchor = $field.closest('.input-group').length ? $field.closest('.input-group') : $field;
            $anchor.after($container);
        });
    }

    function syncTypeUI() {
        var type = $('#voucher-type').val();
        var $valueInput = $('[data-role="discount-value"]');
        var currentValue = $valueInput.val();

        $('#value-prefix').text(type === 'percentage' ? '%' : 'Rp');
        $valueInput.attr('inputmode', type === 'percentage' ? 'decimal' : 'numeric');

        if (type === 'fixed') {
            $valueInput.val(formatRupiah(currentValue));
        } else {
            $valueInput.val(String(currentValue || '').replace(/[^0-9.,]/g, '').replace(',', '.'));
        }
    }

    $('[data-role="money"]').on('input', function () {
        $(this).val(formatRupiah($(this).val()));
    });

    $(document).on('input', '[data-role="discount-value"]', function () {
        if ($('#voucher-type').val() === 'fixed') {
            $(this).val(formatRupiah($(this).val()));
            return;
        }

        var normalized = String($(this).val() || '')
            .replace(/[^0-9.,]/g, '')
            .replace(',', '.');
        var parts = normalized.split('.');
        if (parts.length > 2) {
            normalized = parts.shift() + '.' + parts.join('');
        }

        $(this).val(normalized);
    });

    $('#voucher-type').on('change', syncTypeUI);

    $('[data-role="datepicker"]').datepicker({
        format: 'dd/mm/yyyy',
        autoclose: true,
        todayHighlight: true,
        language: 'id',
        orientation: 'bottom auto'
    });

    $('input[name="valid_from"]').on('changeDate', function (e) {
        $('input[name="valid_until"]').datepicker('setStartDate', e.date);
    });

    $('input[name="valid_until"]').on('changeDate', function (e) {
        $('input[name="valid_from"]').datepicker('setEndDate', e.date);
    });

    var validFromValue = $('input[name="valid_from"]').val();
    var validUntilValue = $('input[name="valid_until"]').val();
    if (validFromValue) {
        $('input[name="valid_until"]').datepicker('setStartDate', validFromValue);
    }
    if (validUntilValue) {
        $('input[name="valid_from"]').datepicker('setEndDate', validUntilValue);
    }

    syncTypeUI();

    @if(! $isEdit)
    $('#btn-regen-code').on('click', function () {
        var rand = '';
        var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        for (var i = 0; i < 8; i++) rand += chars.charAt(Math.floor(Math.random() * chars.length));
        $('input[name=code]').val(rand);
    });
    @endif

    $form.on('submit', function (e) {
        e.preventDefault();
        clearFieldErrors();

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $form.serialize(),
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            beforeSend: function () {
                $submitButton.prop('disabled', true);
            },
            success: function (response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: response.message,
                    timer: 1400,
                    showConfirmButton: false
                }).then(function () {
                    window.location.href = response.redirect || $form.data('redirect');
                });
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    renderFieldErrors(xhr.responseJSON.errors || {});
                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi gagal',
                        text: xhr.responseJSON.message || 'Periksa kembali data voucher.'
                    });
                    return;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Voucher tidak dapat disimpan. Coba lagi.'
                });
            },
            complete: function () {
                $submitButton.prop('disabled', false);
            }
        });
    });
});
</script>
@endpush
