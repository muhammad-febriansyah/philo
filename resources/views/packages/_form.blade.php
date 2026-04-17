@php
    $isEdit = isset($package);
    $selectedTemplateIds = collect(old('template_ids', $isEdit ? $package->templates->pluck('id')->all() : []))
        ->map(fn ($id) => (int) $id)
        ->all();
    $currentPrintSize = old('print_size', $isEdit ? $package->print_size : '');
@endphp

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0">{{ $isEdit ? 'Edit Paket Foto' : 'Tambah Paket Foto' }}</h5>
        <span class="text-muted small">Isi data dasar dulu, lalu pilih template.</span>
    </div>

    <div class="card-body">
        <div class="row g-3">
            <div class="col-lg-5">
                <label for="name" class="form-label">Nama Paket <span class="text-danger">*</span></label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $isEdit ? $package->name : '') }}"
                    placeholder="Contoh: Paket Berdua"
                    required
                >
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-lg-7">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea
                    id="description"
                    name="description"
                    rows="2"
                    class="form-control @error('description') is-invalid @enderror"
                    placeholder="Contoh: 2 foto cetak ukuran strip, cocok untuk berdua."
                >{{ old('description', $isEdit ? $package->description : '') }}</textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-lg-3">
                <label for="photo_count" class="form-label">Jumlah Foto <span class="text-danger">*</span></label>
                <input
                    type="number"
                    id="photo_count"
                    name="photo_count"
                    class="form-control @error('photo_count') is-invalid @enderror"
                    value="{{ old('photo_count', $isEdit ? $package->photo_count : 1) }}"
                    min="1"
                    required
                >
                @error('photo_count') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-lg-3">
                <label for="print_size" class="form-label">Ukuran Cetak <span class="text-danger">*</span></label>
                <select
                    id="print_size"
                    name="print_size"
                    class="form-select @error('print_size') is-invalid @enderror"
                    required
                >
                    <option value="">Pilih ukuran</option>
                    <option value="strip" {{ $currentPrintSize === 'strip' ? 'selected' : '' }}>Strip (2x6 inci)</option>
                    <option value="4R" {{ $currentPrintSize === '4R' ? 'selected' : '' }}>4R (4x6 inci)</option>
                    <option value="A4" {{ $currentPrintSize === 'A4' ? 'selected' : '' }}>A4</option>
                    <option value="A3" {{ $currentPrintSize === 'A3' ? 'selected' : '' }}>A3</option>
                    @if($currentPrintSize && !in_array($currentPrintSize, ['strip', '4R', 'A4', 'A3'], true))
                        <option value="{{ $currentPrintSize }}" selected>{{ $currentPrintSize }}</option>
                    @endif
                </select>
                @error('print_size') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-lg-3">
                <label for="price-display" class="form-label">Harga <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input
                        type="text"
                        id="price-display"
                        class="form-control @error('price') is-invalid @enderror"
                        value="{{ old('price', $isEdit ? $package->price : '') }}"
                        placeholder="0"
                        autocomplete="off"
                    >
                    <input type="hidden" name="price" id="price-raw" value="{{ old('price', $isEdit ? $package->price : '') }}">
                </div>
                @error('price') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>

            <div class="col-lg-3 d-flex align-items-end">
                <div class="form-check form-switch mb-2">
                    <input type="hidden" name="is_active" value="0">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        id="is_active"
                        name="is_active"
                        value="1"
                        {{ old('is_active', $isEdit ? $package->is_active : true) ? 'checked' : '' }}
                    >
                    <label class="form-check-label ms-1" for="is_active">Paket Aktif</label>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mt-3">
    <div class="card-header bg-transparent">
        <h5 class="card-title mb-0">Pilih Template</h5>
        <p class="text-muted mb-0 mt-1 small">Template yang cocok akan aktif otomatis sesuai jumlah foto dan ukuran cetak.</p>
        <div id="template-match-hint" class="small mt-2"></div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-xl-8">
                @if($templates->isEmpty())
                    <div class="alert alert-warning mb-0">
                        Belum ada template aktif. Silakan tambah template terlebih dahulu.
                    </div>
                @else
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
                                        <p class="template-meta mb-2">{{ strtoupper($template->print_size) }} · {{ $template->photo_slots }} slot</p>
                                        <span class="template-status-badge">Cocok</span>
                                        <p class="template-reason mb-0"></p>
                                    </div>
                                    <div class="template-check-indicator">
                                        <i class="mdi mdi-check"></i>
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>
                @endif
                @error('template_ids') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
            </div>

            <div class="col-xl-4">
                <div class="selected-template-panel">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="mb-0">Template Terpilih</h6>
                        <span class="badge bg-primary" id="selected-template-count">0</span>
                    </div>
                    <div id="selected-template-preview" class="selected-template-list">
                        <div class="selected-empty text-muted" id="selected-template-empty">
                            Belum ada template dipilih.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary waves-effect">
        <i class="mdi mdi-content-save me-1"></i> {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Paket' }}
    </button>
    <a href="{{ route('packages.index') }}" class="btn btn-secondary waves-effect">Kembali</a>
</div>
