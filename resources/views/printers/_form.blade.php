@php
    $isEdit = $printer->exists;
    $action = $isEdit ? route('printers.update', $printer) : route('printers.store');
    $currentBranch = old('branch_id', $printer->branch_id ?? (auth()->user()->isCabang() ? auth()->user()->branch_id : ''));
    $currentPurpose = old('purpose', $printer->purpose ?? 'voucher');
    $currentConnector = old('connector', $printer->connector ?? 'file');
    $currentDevice = old('device', $printer->device ?? '');
    $currentProfile = old('profile', $printer->profile ?? 'simple');
    $currentName = old('name', $printer->name ?? '');
    $currentActive = old('is_active', $isEdit ? $printer->is_active : true);
@endphp

<div class="row g-4">
    <div class="col-xl-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex align-items-center gap-2">
                <span class="d-flex align-items-center justify-content-center rounded-2"
                      style="width:32px;height:32px;background:#fff9e0;border:1.5px solid #C9A800;">
                    <i class="fas fa-print" style="color:#C9A800;font-size:15px;"></i>
                </span>
                <h5 class="card-title mb-0 fw-semibold">{{ $isEdit ? 'Edit Printer' : 'Tambah Printer' }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ $action }}" method="POST" id="printer-form">
                    @csrf
                    @if ($isEdit) @method('PUT') @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">Cabang</label>
                            <select name="branch_id" class="form-select" required {{ auth()->user()->isCabang() ? 'disabled' : '' }}>
                                <option value="">-- Pilih cabang --</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected((string) $currentBranch === (string) $branch->id)>
                                        {{ $branch->name }} @if ($branch->code) ({{ $branch->code }}) @endif
                                    </option>
                                @endforeach
                            </select>
                            @if (auth()->user()->isCabang())
                                <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                                <small class="text-muted">Otomatis cabang Anda.</small>
                            @endif
                            @error('branch_id') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">Slot Printer</label>
                            <select name="purpose" class="form-select" required>
                                @foreach ($purposes as $key => $label)
                                    <option value="{{ $key }}" @selected($currentPurpose === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">1 cabang hanya boleh 1 printer per slot.</small>
                            @error('purpose') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small text-dark">Nama / Catatan <span class="text-muted">(opsional)</span></label>
                            <input type="text" name="name" class="form-control" value="{{ $currentName }}"
                                   placeholder="contoh: RPP-02N Kasir Depan" maxlength="100">
                            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small text-dark">Tipe Koneksi</label>
                            <select name="connector" id="connector" class="form-select" required>
                                @foreach ($connectors as $key => $label)
                                    <option value="{{ $key }}" @selected($currentConnector === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted" id="connector-hint"></small>
                            @error('connector') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small text-dark d-flex justify-content-between align-items-center">
                                <span id="device-label">Path Device</span>
                                <button type="button" class="btn btn-sm btn-link p-0" id="btn-rescan">
                                    <i class="fas fa-sync-alt me-1"></i> Rescan
                                </button>
                            </label>
                            <input type="text" name="device" id="device" class="form-control"
                                   value="{{ $currentDevice }}" placeholder="contoh: /dev/cu.RPP02N"
                                   list="device-options" required maxlength="255">
                            <datalist id="device-options">
                                @foreach (($devices['serial'] ?? []) as $path)
                                    <option value="{{ $path }}"></option>
                                @endforeach
                                @foreach (($devices['cups'] ?? []) as $name)
                                    <option value="{{ $name }}"></option>
                                @endforeach
                            </datalist>

                            <div class="mt-2 d-flex flex-wrap gap-2" id="device-suggestions">
                                @foreach (($devices['serial'] ?? []) as $path)
                                    <button type="button" class="btn btn-sm btn-outline-secondary device-pick" data-kind="file" data-value="{{ $path }}">
                                        <i class="fas fa-plug me-1"></i> {{ $path }}
                                    </button>
                                @endforeach
                                @foreach (($devices['cups'] ?? []) as $name)
                                    <button type="button" class="btn btn-sm btn-outline-info device-pick" data-kind="cups" data-value="{{ $name }}">
                                        <i class="fas fa-print me-1"></i> {{ $name }}
                                    </button>
                                @endforeach
                                @if (empty($devices['serial']) && empty($devices['cups']))
                                    <span class="text-muted small">Belum ada device terdeteksi di mesin server. Hubungkan printer lalu klik Rescan.</span>
                                @endif
                            </div>
                            @error('device') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-semibold small text-dark">Profil Printer</label>
                            <select name="profile" class="form-select">
                                @foreach ($profiles as $key => $label)
                                    <option value="{{ $key }}" @selected($currentProfile === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Default <code>simple</code> aman; pakai <code>POS-5890</code> untuk RPP-02N / TM-58V (mini 58mm).</small>
                            @error('profile') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-dark">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ $currentActive ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Aktif</label>
                            </div>
                            <small class="text-muted">Printer nonaktif akan dilewati saat print.</small>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button type="submit" class="btn waves-effect"
                                style="background:#C9A800;color:#1a1200;font-weight:600;border:none;">
                            <i class="fas fa-save me-1"></i> {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Printer' }}
                        </button>
                        <a href="{{ route('printers.index') }}" class="btn btn-outline-secondary waves-effect">
                            <i class="fas fa-times me-1"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="card border-0 shadow-sm" style="background:#fffbe6;border:1px dashed #C9A800 !important;">
            <div class="card-body">
                <h6 class="fw-semibold text-dark mb-2">
                    <i class="fas fa-info-circle text-warning me-1"></i> Tips
                </h6>
                <ul class="text-muted small mb-0" style="padding-left:18px;line-height:1.7;">
                    <li><strong>File / Serial Device</strong> — paling cocok untuk Bluetooth/USB-Serial. Pilih path <code>/dev/cu.*</code> (macOS) atau <code>/dev/usb/lp*</code> (Linux).</li>
                    <li><strong>CUPS Printer</strong> — printer terdaftar di System Settings → Printers. Cek dengan <code>lpstat -p</code>.</li>
                    <li><strong>Network Printer</strong> — printer LAN/WiFi, isi <code>host:port</code> (default 9100).</li>
                    <li>Klik <strong>Rescan</strong> untuk refresh daftar device server.</li>
                    <li>Setelah simpan, klik tombol Test Print di halaman Daftar Printer untuk konfirmasi printer merespon.</li>
                </ul>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body">
                <h6 class="fw-semibold text-dark mb-2">
                    <i class="fas fa-bolt text-success me-1"></i> Cara Kerja
                </h6>
                <ul class="text-muted small mb-0" style="padding-left:18px;line-height:1.7;">
                    <li>Server Laravel mengirim <strong>perintah ESC/POS</strong> langsung ke device — tanpa dialog browser.</li>
                    <li>Library: <code>mike42/escpos-php</code> v4.</li>
                    <li>Setiap cabang punya printer-nya sendiri yang tersimpan di database.</li>
                    <li>Saat user cabang klik tombol Print, server menggunakan printer milik cabang user yang login.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(function () {
    var connectorMeta = {
        file:    { label: 'Path Device', placeholder: '/dev/cu.RPP02N atau /dev/usb/lp0', hint: 'Path filesystem ke device serial / USB.' },
        cups:    { label: 'Nama CUPS Printer', placeholder: 'RPP02N', hint: 'Nama queue di `lpstat -p`.' },
        network: { label: 'Host:Port Printer', placeholder: '192.168.1.50:9100', hint: 'Default port ESC/POS biasanya 9100.' }
    };

    function syncConnectorUi() {
        var key = $('#connector').val();
        var meta = connectorMeta[key] || connectorMeta.file;
        $('#device-label').text(meta.label);
        $('#device').attr('placeholder', meta.placeholder);
        $('#connector-hint').text(meta.hint);
    }

    syncConnectorUi();
    $('#connector').on('change', syncConnectorUi);

    $(document).on('click', '.device-pick', function () {
        var kind = $(this).data('kind');
        var value = $(this).data('value');
        $('#connector').val(kind === 'cups' ? 'cups' : 'file').trigger('change');
        $('#device').val(value);
    });

    $('#btn-rescan').on('click', function () {
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Scan...');
        $.getJSON('{{ route('printers.scan') }}')
            .done(function (data) {
                var serial = data.serial || [];
                var cups = data.cups || [];
                var $list = $('#device-suggestions').empty();
                var $datalist = $('#device-options').empty();
                serial.forEach(function (path) {
                    $('<button type="button" class="btn btn-sm btn-outline-secondary device-pick">')
                        .attr('data-kind', 'file').attr('data-value', path)
                        .html('<i class="fas fa-plug me-1"></i> ' + path).appendTo($list);
                    $('<option>').attr('value', path).appendTo($datalist);
                });
                cups.forEach(function (name) {
                    $('<button type="button" class="btn btn-sm btn-outline-info device-pick">')
                        .attr('data-kind', 'cups').attr('data-value', name)
                        .html('<i class="fas fa-print me-1"></i> ' + name).appendTo($list);
                    $('<option>').attr('value', name).appendTo($datalist);
                });
                if (!serial.length && !cups.length) {
                    $list.html('<span class="text-muted small">Belum ada device terdeteksi.</span>');
                }
            })
            .always(function () { $btn.prop('disabled', false).html('<i class="fas fa-sync-alt me-1"></i> Rescan'); });
    });
});
</script>
@endpush
