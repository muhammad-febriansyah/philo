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
    <div class="col-xl-12">
        <form action="{{ route('templates.store') }}" method="POST" enctype="multipart/form-data" id="tmpl-form">
            @csrf
            <input type="hidden" name="slot_positions" id="slot-positions-json" value="[]">

            <div class="row g-4">

                {{-- ── LEFT: Metadata + Thumbnail ── --}}
                <div class="col-lg-6">

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
                                <input type="hidden" name="print_size" id="print-size-value" value="{{ old('print_size') }}">
                                <div class="row g-2 mt-1">
                                    @php
                                        $printSizes = [
                                            'strip' => ['label' => 'Strip', 'desc' => '2×6 inci', 'w' => 24, 'h' => 72],
                                            '4R'    => ['label' => '4R',   'desc' => '4×6 inci', 'w' => 40, 'h' => 60],
                                            'A4'    => ['label' => 'A4',   'desc' => '210×297 mm', 'w' => 46, 'h' => 65],
                                            'A3'    => ['label' => 'A3',   'desc' => '297×420 mm', 'w' => 52, 'h' => 74],
                                        ];
                                    @endphp
                                    @foreach($printSizes as $val => $opt)
                                    <div class="col-6">
                                        <div class="print-size-option {{ old('print_size') == $val ? 'selected' : '' }}"
                                             data-value="{{ $val }}" onclick="selectPrintSize(this)">
                                            <div class="print-size-paper" style="width:{{ $opt['w'] }}px;height:{{ $opt['h'] }}px;"></div>
                                            <div class="print-size-label">{{ $opt['label'] }}</div>
                                            <div class="print-size-desc">{{ $opt['desc'] }}</div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @error('print_size') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
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
                            <h6 class="card-title mb-0 fw-bold">Gambar Frame <span class="text-danger">*</span></h6>
                        </div>
                        <div class="card-body pt-2">
                            <div class="upload-zone" id="zone-thumbnail">
                                <input type="file" id="input-thumbnail" name="thumbnail" accept="image/*" required>
                                <div class="zone-placeholder" id="ph-thumbnail">
                                    <i class="mdi mdi-image-plus"></i>
                                    <p>Klik atau seret</p>
                                    <span>JPG, PNG, WebP — maks. 10 MB</span>
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

                {{-- ── RIGHT: Slot Position Editor ── --}}
                <div class="col-lg-6">
                    @include('templates._slot-editor')
                    @error('slot_positions')
                        <div class="alert alert-danger mt-3 mb-0">{{ $message }}</div>
                    @enderror
                </div>

            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
@include('templates._upload-scripts')
<script>
    function selectPrintSize(el) {
        document.querySelectorAll('.print-size-option').forEach(c => c.classList.remove('selected'));
        el.classList.add('selected');
        var input = document.getElementById('print-size-value');
        input.value = el.dataset.value;
        input.dispatchEvent(new Event('change'));
    }

    // Init thumbnail zone
    initZone('zone-thumbnail', 'input-thumbnail', 'prev-thumbnail', 'prev-thumbnail-img', 'clear-thumbnail', 'ph-thumbnail');
</script>
@endpush
