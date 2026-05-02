@php
    $isEdit = isset($package);
    $selectedTemplateIds = collect(old('template_ids', $isEdit ? $package->templates->pluck('id')->all() : []))
        ->map(fn ($id) => (int) $id)
        ->all();
    $currentPrintCopies = (int) old('print_copies', $isEdit ? $package->print_copies : 1);

    $sizeLabels = [
        'strip' => 'Photo Strip',
        '4R' => '4R',
        'A4' => 'A4',
        'A3' => 'A3',
    ];
@endphp

<div class="pkg-form-shell">
    {{-- ─────────── Section 1: Detail Paket ─────────── --}}
    <section class="pkg-section">
        <div class="pkg-section-head">
            <div class="pkg-section-num">1</div>
            <div>
                <h5 class="pkg-section-title">Detail Paket</h5>
                <p class="pkg-section-sub">Beri nama paket dan deskripsi singkat untuk pelanggan.</p>
            </div>
        </div>

        <div class="pkg-section-body">
            <div class="row g-3">
                <div class="col-lg-5">
                    <label for="name" class="pkg-label">Nama Paket <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control pkg-input @error('name') is-invalid @enderror"
                        value="{{ old('name', $isEdit ? $package->name : '') }}"
                        placeholder="Contoh: Paket Berdua"
                        required
                    >
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-lg-7">
                    <label for="description" class="pkg-label">
                        Deskripsi <span class="pkg-label-hint">(opsional)</span>
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        rows="2"
                        class="form-control pkg-input @error('description') is-invalid @enderror"
                        placeholder="Contoh: 2 foto cetak ukuran strip, cocok untuk berdua."
                    >{{ old('description', $isEdit ? $package->description : '') }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label class="pkg-toggle-row">
                        <input type="hidden" name="is_active" value="0">
                        <input
                            class="pkg-toggle-input"
                            type="checkbox"
                            id="is_active"
                            name="is_active"
                            value="1"
                            {{ old('is_active', $isEdit ? $package->is_active : true) ? 'checked' : '' }}
                        >
                        <span class="pkg-toggle-switch"></span>
                        <span class="pkg-toggle-text">
                            <span class="pkg-toggle-title">Paket Aktif</span>
                            <span class="pkg-toggle-desc">Aktifkan agar paket muncul di booth dan bisa dipilih pelanggan.</span>
                        </span>
                    </label>
                </div>
            </div>
        </div>
    </section>

    {{-- ─────────── Section 2: Harga & Lembar Cetak ─────────── --}}
    <section class="pkg-section">
        <div class="pkg-section-head">
            <div class="pkg-section-num">2</div>
            <div>
                <h5 class="pkg-section-title">Harga & Cetakan</h5>
                <p class="pkg-section-sub">Berapa pelanggan bayar dan jumlah lembar yang dicetak.</p>
            </div>
        </div>

        <div class="pkg-section-body">
            <div class="row g-3 align-items-start">
                <div class="col-lg-6">
                    <label for="price-display" class="pkg-label">Harga Paket <span class="text-danger">*</span></label>
                    <div class="pkg-price-input-wrap">
                        <span class="pkg-price-prefix">Rp</span>
                        <input
                            type="text"
                            id="price-display"
                            class="pkg-price-input @error('price') is-invalid @enderror"
                            value="{{ old('price', $isEdit ? $package->price : '') }}"
                            placeholder="50.000"
                            autocomplete="off"
                            inputmode="numeric"
                        >
                        <input type="hidden" name="price" id="price-raw" value="{{ old('price', $isEdit ? $package->price : '') }}">
                    </div>
                    <div class="pkg-help-text mt-2">
                        <i class="mdi mdi-information-outline"></i> Harga yang dibayar pelanggan untuk paket ini.
                    </div>
                    @error('price') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="col-lg-6">
                    <label class="pkg-label">
                        Berapa lembar yang dicetak? <span class="text-danger">*</span>
                    </label>
                    <div class="pkg-chip-group" data-target="print_copies">
                        @foreach([1, 2, 3, 4, 5] as $opt)
                            <button type="button" class="pkg-chip-btn {{ $currentPrintCopies === $opt ? 'active' : '' }}" data-value="{{ $opt }}">
                                {{ $opt }} lembar
                            </button>
                        @endforeach
                        <input type="hidden" id="print_copies" name="print_copies" value="{{ $currentPrintCopies }}">
                    </div>
                    <div class="pkg-help-text mt-2">
                        <i class="mdi mdi-information-outline"></i> Jumlah salinan kertas keluar dari printer per sesi.
                    </div>
                    @error('print_copies') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
            </div>

        </div>
    </section>

    {{-- ─────────── Section 3: Pilih Template ─────────── --}}
    <section class="pkg-section">
        <div class="pkg-section-head">
            <div class="pkg-section-num">3</div>
            <div>
                <h5 class="pkg-section-title">Pilih Template</h5>
                <p class="pkg-section-sub">Pilih satu atau lebih template untuk paket ini. <strong>Ukuran cetak & jumlah foto otomatis ikut template.</strong></p>
            </div>
        </div>

        <div class="pkg-section-body">
            @if($templates->isEmpty())
                <div class="alert alert-warning mb-0 d-flex align-items-center gap-2">
                    <i class="mdi mdi-alert-circle-outline" style="font-size:1.5rem;"></i>
                    <div>
                        <strong>Belum ada template aktif.</strong><br>
                        <small>Tambah template terlebih dahulu di menu Template / Frame.</small>
                    </div>
                </div>
            @else
                <div id="template-match-hint" class="pkg-template-hint mb-3">
                    <i class="mdi mdi-information-outline"></i>
                    <span>Pilih template yang akan dipakai untuk paket ini. Setelah memilih satu, template lain dengan ukuran berbeda akan dinonaktifkan otomatis.</span>
                </div>

                <div class="template-grid" id="template-grid">
                    @foreach($templates as $template)
                        @php
                            $isChecked = in_array((int) $template->id, $selectedTemplateIds, true);
                            $previewPath = $template->thumbnail_path ?: $template->frame_path;
                            $previewUrl = $previewPath ? \Illuminate\Support\Facades\Storage::url($previewPath) : null;
                        @endphp
                        <div
                            class="template-card {{ $isChecked ? 'selected' : '' }}"
                            data-template-id="{{ $template->id }}"
                            data-template-name="{{ $template->name }}"
                            data-template-size="{{ strtoupper($template->print_size) }}"
                            data-template-slots="{{ $template->photo_slots }}"
                            data-template-preview="{{ $previewUrl }}"
                        >
                            <input
                                class="template-checkbox"
                                type="checkbox"
                                name="template_ids[]"
                                value="{{ $template->id }}"
                                id="tpl-{{ $template->id }}"
                                data-print-size="{{ strtoupper($template->print_size) }}"
                                data-photo-slots="{{ $template->photo_slots }}"
                                {{ $isChecked ? 'checked' : '' }}
                            >

                            <label class="template-card-body" for="tpl-{{ $template->id }}">
                                <div class="template-thumb">
                                    @if($previewUrl)
                                        <img src="{{ $previewUrl }}" alt="{{ $template->name }}">
                                    @else
                                        <div class="template-thumb-empty">
                                            <i class="mdi mdi-image-outline"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="template-content">
                                    <h6 class="template-title mb-1">{{ $template->name }}</h6>
                                    <p class="template-meta mb-2">
                                        <span class="template-size-badge">{{ $sizeLabels[$template->print_size] ?? strtoupper($template->print_size) }}</span>
                                        <span class="template-slot-info">{{ $template->photo_slots }} foto</span>
                                    </p>
                                    <p class="template-reason mb-0"></p>
                                </div>
                                <div class="template-check-indicator">
                                    <i class="mdi mdi-check"></i>
                                </div>
                            </label>
                        </div>
                    @endforeach
                </div>

                <div class="pkg-selected-preview mt-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="mb-0">Template Terpilih</h6>
                        <span class="badge bg-dark" id="selected-template-count">0</span>
                    </div>
                    <div id="selected-template-preview" class="selected-template-list">
                        <div class="selected-empty text-muted small" id="selected-template-empty">
                            Belum ada template dipilih.
                        </div>
                    </div>
                </div>
            @endif
            @error('template_ids') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
        </div>
    </section>

    {{-- ─────────── Action Buttons ─────────── --}}
    <div class="pkg-action-bar">
        <a href="{{ route('packages.index') }}" class="btn btn-outline-secondary btn-lg">
            <i class="mdi mdi-arrow-left me-1"></i> Batal
        </a>
        <button type="submit" class="btn btn-dark btn-lg pkg-submit">
            <i class="mdi mdi-content-save me-1"></i>
            {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Paket' }}
        </button>
    </div>
</div>
