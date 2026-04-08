@extends('layouts.admin')

@section('title', 'Edit Template — ' . $template->name)
@section('page-title', 'Edit Template')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('templates.index') }}">Template / Frame</a></li>
    <li class="breadcrumb-item active">Edit: {{ $template->name }}</li>
@endsection

@push('styles')
@include('templates._upload-styles')
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-11">
        <form action="{{ route('templates.update', $template) }}" method="POST" enctype="multipart/form-data" id="tmpl-form">
            @csrf
            @method('PUT')
            <input type="hidden" name="slot_positions" id="slot-positions-json" value="{{ json_encode($template->slot_positions ?? []) }}">
            <input type="hidden" name="remove_thumbnail" id="remove-thumbnail" value="0">

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
                                    value="{{ old('name', $template->name) }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">Ukuran Cetak <span class="text-danger">*</span></label>
                                <select name="print_size" class="form-select @error('print_size') is-invalid @enderror" required>
                                    <option value="">-- Pilih Ukuran --</option>
                                    @foreach(['strip' => 'Photo Strip (2×6")', '4R' => '4R', 'A4' => 'A4', 'A3' => 'A3'] as $val => $label)
                                        <option value="{{ $val }}" {{ old('print_size', $template->print_size) == $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('print_size') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">Jumlah Slot Foto <span class="text-danger">*</span></label>
                                <input type="number" name="photo_slots" class="form-control @error('photo_slots') is-invalid @enderror"
                                    min="1" max="20" value="{{ old('photo_slots', $template->photo_slots) }}" required>
                                <div class="form-text">Harus sesuai jumlah kotak di editor.</div>
                                @error('photo_slots') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <div class="form-check form-switch form-switch-md">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                                        {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
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
                                <div class="zone-placeholder {{ $template->thumbnail_url ? 'd-none' : '' }}" id="ph-thumbnail">
                                    <i class="mdi mdi-image-plus"></i>
                                    <p>Klik atau seret</p>
                                    <span>JPG, PNG, WebP — maks. 2 MB</span>
                                </div>
                                <div class="zone-preview {{ $template->thumbnail_url ? '' : 'd-none' }}" id="prev-thumbnail">
                                    <img id="prev-thumbnail-img" src="{{ $template->thumbnail_url }}" alt="">
                                    <button type="button" class="btn btn-danger btn-sm rounded-circle btn-zone-clear" id="clear-thumbnail">
                                        <i class="mdi mdi-close"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary waves-effect waves-light px-4 flex-grow-1">
                            <i class="mdi mdi-content-save-outline me-1"></i> Simpan Perubahan
                        </button>
                        <a href="{{ route('templates.index') }}" class="btn btn-secondary waves-effect px-3">Batal</a>
                    </div>
                </div>

                {{-- ── RIGHT: Fabric.js Editor ── --}}
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm overflow-hidden">

                        {{-- Toolbar --}}
                        <div class="editor-toolbar">
                            {{-- Frame replacement picker --}}
                            <div style="position:relative; display:inline-block; overflow:hidden;">
                                <input type="file" id="input-frame" name="frame" accept="image/png,image/webp"
                                    style="position:absolute;inset:0;opacity:0;cursor:pointer;z-index:2;">
                                <button type="button" class="btn btn-soft-primary btn-sm waves-effect">
                                    <i class="mdi mdi-image-frame me-1"></i> Ganti Frame
                                </button>
                            </div>
                            <span id="frame-filename" class="text-muted font-size-12 text-truncate" style="max-width:130px;">
                                {{ $template->frame_path ? basename($template->frame_path) : 'Tidak ada' }}
                            </span>

                            <div class="vr mx-1"></div>

                            <button type="button" class="btn btn-sm btn-outline-secondary waves-effect mode-btn active"
                                data-mode="draw" onclick="setMode('draw')" title="Mode Gambar">
                                <i class="mdi mdi-pencil-box-outline me-1"></i> Gambar Slot
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary waves-effect mode-btn"
                                data-mode="select" onclick="setMode('select')" title="Mode Pilih / Resize">
                                <i class="mdi mdi-cursor-move me-1"></i> Pilih / Pindah
                            </button>

                            <div class="vr mx-1"></div>

                            <button type="button" class="btn btn-sm btn-outline-danger waves-effect" onclick="deleteSelected()" title="Hapus slot dipilih">
                                <i class="mdi mdi-trash-can-outline"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-soft-danger waves-effect" onclick="clearAllSlots()">
                                <i class="mdi mdi-delete-sweep-outline me-1"></i> Reset
                            </button>

                            <span id="slot-count-badge" class="ms-auto badge bg-warning text-dark font-size-13 px-3">
                                {{ count($template->slot_positions ?? []) }} slot
                            </span>
                        </div>

                        @error('frame')
                            <div class="px-3 pt-2 text-danger small">{{ $message }}</div>
                        @enderror

                        {{-- Canvas area --}}
                        <div id="fabric-canvas-wrapper">
                            <div id="editor-placeholder" style="{{ $template->frame_url ? 'display:none' : '' }}">
                                <i class="mdi mdi-image-frame"></i>
                                <p>Ganti frame dengan upload baru, atau<br>
                                   Slot di bawah sudah di-load dari frame yang ada.</p>
                            </div>
                            <div id="slot-labels-container"></div>
                            <canvas id="fabric-canvas"></canvas>
                        </div>

                        <div class="editor-tips">
                            <span><i class="mdi mdi-pencil-box-outline me-1 text-primary"></i><b>Mode Gambar:</b> klik+seret → slot baru</span>
                            <span><i class="mdi mdi-cursor-move me-1 text-success"></i><b>Mode Pilih:</b> drag/resize, Delete → hapus</span>
                            <span><i class="mdi mdi-information-outline me-1 text-warning"></i>Ganti frame akan reset semua slot</span>
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
    initZone('zone-thumbnail', 'input-thumbnail', 'prev-thumbnail', 'prev-thumbnail-img', 'clear-thumbnail', 'ph-thumbnail', true);

    // Init Fabric editor
    editorInit('fabric-canvas');

    // Pre-load existing frame + slots
    @if($template->frame_url)
    editorLoadFrame('{{ $template->frame_url }}');
    editorLoadSlots(@json($template->slot_positions ?? []));
    @endif

    // Frame file replacement
    document.getElementById('input-frame').addEventListener('change', function () {
        var file = this.files[0];
        if (!file) return;
        document.getElementById('frame-filename').textContent = file.name;
        clearAllSlots(); // reset slots since frame changed
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
