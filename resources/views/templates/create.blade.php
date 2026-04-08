@extends('layouts.admin')

@section('title', 'Tambah Template')
@section('page-title', 'Tambah Template')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('templates.index') }}">Template / Frame</a></li>
    <li class="breadcrumb-item active">Tambah</li>
@endsection

@push('styles')
@include('templates._upload-styles')
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-11">
        <form action="{{ route('templates.store') }}" method="POST" enctype="multipart/form-data" id="tmpl-form">
            @csrf
            <input type="hidden" name="slot_positions" id="slot-positions-json" value="[]">

            <div class="row g-4">

                {{-- ── LEFT: Metadata + Thumbnail ── --}}
                <div class="col-lg-4">

                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-transparent">
                            <h6 class="card-title mb-0 fw-bold">Informasi Template</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">Nama Template <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}" placeholder="Contoh: Frame Bunga 4R" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">Ukuran Cetak <span class="text-danger">*</span></label>
                                <select name="print_size" class="form-select @error('print_size') is-invalid @enderror" required>
                                    <option value="">-- Pilih Ukuran --</option>
                                    @foreach(['strip' => 'Photo Strip (2×6")', '4R' => '4R', 'A4' => 'A4', 'A3' => 'A3'] as $val => $label)
                                        <option value="{{ $val }}" {{ old('print_size') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('print_size') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">Jumlah Slot Foto <span class="text-danger">*</span></label>
                                <input type="number" name="photo_slots" class="form-control @error('photo_slots') is-invalid @enderror"
                                    min="1" max="20" value="{{ old('photo_slots', 1) }}" required>
                                <div class="form-text">Gambar kotak di editor sesuai jumlah ini.</div>
                                @error('photo_slots') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <div class="form-check form-switch form-switch-md">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                                        {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-dark ms-1" for="is_active">Template Aktif</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-transparent">
                            <h6 class="card-title mb-0 fw-bold">Thumbnail <span class="text-muted fw-normal small">(opsional)</span></h6>
                        </div>
                        <div class="card-body pt-2">
                            <div class="upload-zone" id="zone-thumbnail">
                                <input type="file" id="input-thumbnail" name="thumbnail" accept="image/*">
                                <div class="zone-placeholder" id="ph-thumbnail">
                                    <i class="mdi mdi-image-plus"></i>
                                    <p>Klik atau seret</p>
                                    <span>JPG, PNG, WebP — maks. 2 MB</span>
                                </div>
                                <div class="zone-preview" id="prev-thumbnail">
                                    <img id="prev-thumbnail-img" src="" alt="">
                                    <button type="button" class="btn btn-danger btn-sm rounded-circle btn-zone-clear" id="clear-thumbnail">
                                        <i class="mdi mdi-close"></i>
                                    </button>
                                </div>
                            </div>
                            @error('thumbnail') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary waves-effect waves-light px-4 flex-grow-1">
                            <i class="mdi mdi-content-save-outline me-1"></i> Simpan Template
                        </button>
                        <a href="{{ route('templates.index') }}" class="btn btn-secondary waves-effect px-3">Batal</a>
                    </div>
                </div>

                {{-- ── RIGHT: Fabric.js Editor ── --}}
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm overflow-hidden">

                        {{-- Toolbar --}}
                        <div class="editor-toolbar">
                            {{-- Frame file picker --}}
                            <div style="position:relative; display:inline-block; overflow:hidden;">
                                <input type="file" id="input-frame" name="frame" accept="image/png,image/webp"
                                    style="position:absolute;inset:0;opacity:0;cursor:pointer;z-index:2;">
                                <button type="button" class="btn btn-primary btn-sm waves-effect">
                                    <i class="mdi mdi-image-frame me-1"></i> Upload Frame
                                </button>
                            </div>
                            <span id="frame-filename" class="text-muted font-size-12 text-truncate" style="max-width:140px;">
                                Belum ada file
                            </span>

                            <div class="vr mx-1"></div>

                            {{-- Mode buttons --}}
                            <button type="button" class="btn btn-sm btn-outline-secondary waves-effect mode-btn active"
                                data-mode="draw" onclick="setMode('draw')" title="Mode Gambar: klik+seret untuk tambah slot">
                                <i class="mdi mdi-pencil-box-outline me-1"></i> Gambar Slot
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary waves-effect mode-btn"
                                data-mode="select" onclick="setMode('select')" title="Mode Pilih: drag & resize slot yang ada">
                                <i class="mdi mdi-cursor-move me-1"></i> Pilih / Pindah
                            </button>

                            <div class="vr mx-1"></div>

                            {{-- Actions --}}
                            <button type="button" class="btn btn-sm btn-outline-danger waves-effect" onclick="deleteSelected()" title="Hapus slot yang dipilih">
                                <i class="mdi mdi-trash-can-outline"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-soft-danger waves-effect" onclick="clearAllSlots()" title="Hapus semua slot">
                                <i class="mdi mdi-delete-sweep-outline me-1"></i> Reset
                            </button>

                            <span id="slot-count-badge" class="ms-auto badge bg-warning text-dark font-size-13 px-3">0 slot</span>
                        </div>

                        @error('frame')
                            <div class="px-3 pt-2 text-danger small">{{ $message }}</div>
                        @enderror

                        {{-- Canvas area --}}
                        <div id="fabric-canvas-wrapper">
                            <div id="editor-placeholder">
                                <i class="mdi mdi-image-frame"></i>
                                <p>Upload frame PNG/WebP untuk mulai.<br>
                                   Kemudian <strong style="color:#f9c846;">klik + seret</strong> untuk menggambar posisi slot foto.</p>
                            </div>
                            <div id="slot-labels-container"></div>
                            <canvas id="fabric-canvas"></canvas>
                        </div>

                        <div class="editor-tips">
                            <span><i class="mdi mdi-pencil-box-outline me-1 text-primary"></i><b>Mode Gambar:</b> klik+seret → slot baru</span>
                            <span><i class="mdi mdi-cursor-move me-1 text-success"></i><b>Mode Pilih:</b> drag/resize slot, Delete → hapus</span>
                            <span><i class="mdi mdi-ruler me-1 text-warning"></i>Koordinat disimpan dalam piksel asli frame</span>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js"></script>
@include('templates._upload-scripts')
@include('templates._slot-scripts')
<script>
    // Init thumbnail zone
    initZone('zone-thumbnail', 'input-thumbnail', 'prev-thumbnail', 'prev-thumbnail-img', 'clear-thumbnail', 'ph-thumbnail');

    // Init Fabric editor
    editorInit('fabric-canvas');

    // Frame file picker
    document.getElementById('input-frame').addEventListener('change', function () {
        var file = this.files[0];
        if (!file) return;
        document.getElementById('frame-filename').textContent = file.name;
        var reader = new FileReader();
        reader.onload = function (e) { editorLoadFrame(e.target.result); };
        reader.readAsDataURL(file);
    });

    // Sync JSON on submit
    document.getElementById('tmpl-form').addEventListener('submit', function () {
        _syncJson();
    });
</script>
@endpush
