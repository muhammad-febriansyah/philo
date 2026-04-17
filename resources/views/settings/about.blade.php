@extends('layouts.admin')

@section('title', 'Tentang Kami')
@section('page-title', 'Tentang Kami')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Tentang Kami</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <form action="{{ route('settings.about.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-lg-8">

                    <!-- Informasi Utama -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-transparent border-bottom p-3">
                            <h5 class="card-title mb-0 text-dark">Informasi Utama</h5>
                        </div>
                        <div class="card-body p-4">

                            <div class="mb-3">
                                <label class="form-label text-dark fw-bold">Judul Halaman <span class="text-danger">*</span></label>
                                <input type="text" name="about_title"
                                    class="form-control @error('about_title') is-invalid @enderror"
                                    value="{{ old('about_title', $settings['about_title'] ?? '') }}"
                                    placeholder="Contoh: Tentang Philo Photobooth" required>
                                @error('about_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-dark fw-bold">Tagline / Subtitle</label>
                                <input type="text" name="about_tagline"
                                    class="form-control @error('about_tagline') is-invalid @enderror"
                                    value="{{ old('about_tagline', $settings['about_tagline'] ?? '') }}"
                                    placeholder="Contoh: Abadikan Momen, Cetak Kenangan">
                                @error('about_tagline') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-dark fw-bold">Deskripsi</label>
                                <textarea name="about_description" id="about_description"
                                    class="@error('about_description') is-invalid @enderror">{{ old('about_description', $settings['about_description'] ?? '') }}</textarea>
                                @error('about_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Visi & Misi -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-transparent border-bottom p-3">
                            <h5 class="card-title mb-0 text-dark">Visi & Misi</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="form-label text-dark fw-bold">Visi</label>
                                <textarea name="about_vision" id="about_vision"
                                    class="@error('about_vision') is-invalid @enderror">{{ old('about_vision', $settings['about_vision'] ?? '') }}</textarea>
                                @error('about_vision') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-0">
                                <label class="form-label text-dark fw-bold">Misi</label>
                                <textarea name="about_mission" id="about_mission"
                                    class="@error('about_mission') is-invalid @enderror">{{ old('about_mission', $settings['about_mission'] ?? '') }}</textarea>
                                @error('about_mission') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Statistik -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-transparent border-bottom p-3">
                            <h5 class="card-title mb-0 text-dark">Statistik Pencapaian</h5>
                            <small class="text-muted">Angka yang ditampilkan di halaman Tentang Kami</small>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label text-dark fw-bold">Tahun Berdiri</label>
                                    <input type="number" name="about_founded_year"
                                        class="form-control @error('about_founded_year') is-invalid @enderror"
                                        value="{{ old('about_founded_year', $settings['about_founded_year'] ?? '') }}"
                                        placeholder="2020" min="2000" max="{{ date('Y') }}">
                                    @error('about_founded_year') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label text-dark fw-bold">Total Sesi Foto</label>
                                    <input type="text" name="about_total_sessions"
                                        class="form-control @error('about_total_sessions') is-invalid @enderror"
                                        value="{{ old('about_total_sessions', $settings['about_total_sessions'] ?? '') }}"
                                        placeholder="10.000+">
                                    @error('about_total_sessions') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label text-dark fw-bold">Total Cabang</label>
                                    <input type="text" name="about_total_branches"
                                        class="form-control @error('about_total_branches') is-invalid @enderror"
                                        value="{{ old('about_total_branches', $settings['about_total_branches'] ?? '') }}"
                                        placeholder="10+">
                                    @error('about_total_branches') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label text-dark fw-bold">Klien Puas</label>
                                    <input type="text" name="about_total_clients"
                                        class="form-control @error('about_total_clients') is-invalid @enderror"
                                        value="{{ old('about_total_clients', $settings['about_total_clients'] ?? '') }}"
                                        placeholder="5.000+">
                                    @error('about_total_clients') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <small class="text-muted">Anda bisa menggunakan format bebas seperti <code>10.000+</code> atau <code>Sejak 2020</code>.</small>
                        </div>
                    </div>

                </div>

                <div class="col-lg-4">

                    <!-- Foto Hero -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-transparent border-bottom p-3">
                            <h5 class="card-title mb-0 text-dark">Foto Hero / Banner</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3 text-center">
                                <div id="hero-preview-container"
                                    class="bg-light rounded border d-flex align-items-center justify-content-center overflow-hidden mb-3"
                                    style="width: 100%; height: 180px; border-style: dashed !important; border-width: 2px !important;">
                                    @if(!empty($settings['about_hero_image_path']))
                                        <img src="{{ Storage::url($settings['about_hero_image_path']) }}"
                                            id="hero-preview" class="img-fluid" style="max-height: 180px; object-fit: cover; width: 100%;" alt="Hero">
                                        <div class="text-muted text-center d-none" id="hero-placeholder">
                                            <i class="mdi mdi-image-plus-outline font-size-36 d-block"></i>
                                            <span class="font-size-12">Klik untuk upload gambar</span>
                                        </div>
                                    @else
                                        <div class="text-muted text-center" id="hero-placeholder">
                                            <i class="mdi mdi-image-plus-outline font-size-36 d-block"></i>
                                            <span class="font-size-12">Klik untuk upload gambar</span>
                                        </div>
                                        <img id="hero-preview" class="img-fluid d-none" style="max-height: 180px; object-fit: cover; width: 100%;" alt="Hero">
                                    @endif
                                </div>
                                <input type="file" name="about_hero_image" id="hero-image-input" class="d-none" accept="image/*">
                                <input type="hidden" name="remove_hero_image" id="remove-hero-image" value="0">
                                <button type="button" class="btn btn-sm btn-primary waves-effect mb-2 w-100 rounded-pill"
                                    onclick="document.getElementById('hero-image-input').click()">
                                    <i class="mdi mdi-upload me-1"></i> Pilih Gambar
                                </button>
                                <button type="button" id="btn-remove-hero"
                                    class="btn btn-sm btn-link text-danger waves-effect w-100 {{ !empty($settings['about_hero_image_path']) ? '' : 'd-none' }}">
                                    <i class="mdi mdi-trash-can-outline me-1"></i> Hapus Gambar
                                </button>
                            </div>
                            <small class="text-muted">Format JPG/PNG/WebP. Disarankan rasio 16:9. Maks 3MB.</small>
                        </div>
                    </div>

                    <!-- Simpan -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <button type="submit" class="btn btn-primary waves-effect waves-light w-100">
                                <i class="mdi mdi-content-save-outline me-1"></i> Simpan Perubahan
                            </button>
                            <a href="{{ route('settings.about') }}" class="btn btn-light waves-effect w-100 mt-2">
                                <i class="mdi mdi-refresh me-1"></i> Reset
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/libs/tinymce/tinymce.min.js') }}"></script>
<script>
    tinymce.init({
        selector: '#about_description, #about_vision, #about_mission',
        base_url: '{{ asset('assets/libs/tinymce') }}',
        suffix: '.min',
        menubar: false,
        plugins: 'lists link',
        toolbar: 'bold italic underline | bullist numlist | link | removeformat',
        promotion: false,
        branding: false,
        resize: false,
    });

    // Hero image preview
    document.getElementById('hero-image-input').addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function (ev) {
            document.getElementById('hero-preview').src = ev.target.result;
            document.getElementById('hero-preview').classList.remove('d-none');
            document.getElementById('hero-placeholder').classList.add('d-none');
            document.getElementById('btn-remove-hero').classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    });

    // Remove hero image
    document.getElementById('btn-remove-hero').addEventListener('click', function () {
        document.getElementById('hero-image-input').value = '';
        document.getElementById('remove-hero-image').value = '1';
        document.getElementById('hero-preview').classList.add('d-none');
        document.getElementById('hero-preview').src = '';
        document.getElementById('hero-placeholder').classList.remove('d-none');
        this.classList.add('d-none');
    });
</script>
@endpush
