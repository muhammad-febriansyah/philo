@extends('layouts.admin')

@section('title', 'Pengaturan Umum')
@section('page-title', 'Pengaturan Umum')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Pengaturan Umum</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <form action="{{ route('settings.general.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-lg-8">
                    <!-- Branding & Site Info -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-transparent border-bottom p-3">
                            <h5 class="card-title mb-0 text-dark">Informasi Situs & Branding</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3 mb-md-0 border-end border-light">
                                    <label class="form-label text-dark fw-bold">Logo Utama</label>
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <div id="logo-preview-container" class="bg-light rounded-circle border d-flex align-items-center justify-content-center overflow-hidden shadow-sm" style="width: 110px; height: 110px; border-style: dashed !important; border-width: 2px !important;">
                                                @if($settings['logo_path'])
                                                    <img src="{{ Storage::url($settings['logo_path']) }}" id="logo-preview" class="img-fluid" style="max-height: 100%;" alt="Logo">
                                                    <div class="text-muted text-center d-none" id="logo-placeholder">
                                                        <i class="mdi mdi-camera-plus-outline font-size-24"></i>
                                                    </div>
                                                @else
                                                    <div class="text-muted text-center" id="logo-placeholder">
                                                        <i class="mdi mdi-camera-plus-outline font-size-24"></i>
                                                    </div>
                                                    <img id="logo-preview" class="img-fluid d-none" style="max-height: 100%;" alt="Logo">
                                                @endif
                                            </div>
                                        </div>
                                        <div>
                                            <input type="file" name="logo" id="logo-input" class="d-none" accept="image/*">
                                            <input type="hidden" name="remove_logo" id="remove-logo" value="0">
                                            <button type="button" class="btn btn-sm btn-primary waves-effect mb-2 w-100 rounded-pill" onclick="document.getElementById('logo-input').click()">
                                                <i class="mdi mdi-upload me-1"></i> Ganti Logo
                                            </button>
                                            <button type="button" class="btn btn-sm btn-link text-danger waves-effect w-100 {{ $settings['logo_path'] ? '' : 'd-none' }}" id="btn-remove-logo">
                                                <i class="mdi mdi-trash-can-outline me-1"></i> Hapus
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mt-2 text-muted font-size-12">Rekomendasi format PNG/WebP, Max 2MB.</div>
                                </div>
                                <div class="col-md-6 ms-auto">
                                    <label class="form-label text-dark fw-bold ps-md-4">Favicon</label>
                                    <div class="d-flex align-items-center ps-md-4">
                                        <div class="me-3">
                                            <div id="favicon-preview-container" class="bg-light rounded border d-flex align-items-center justify-content-center overflow-hidden shadow-sm" style="width: 60px; height: 60px;">
                                                @if($settings['favicon_path'])
                                                    <img src="{{ Storage::url($settings['favicon_path']) }}" id="favicon-preview" style="width: 32px; height: 32px; object-fit: contain;" alt="Favicon">
                                                    <div class="text-muted text-center d-none" id="favicon-placeholder">
                                                        <i class="mdi mdi-image-outline font-size-18"></i>
                                                    </div>
                                                @else
                                                    <div class="text-muted text-center" id="favicon-placeholder">
                                                        <i class="mdi mdi-image-outline font-size-18"></i>
                                                    </div>
                                                    <img id="favicon-preview" style="width: 32px; height: 32px; object-fit: contain;" class="d-none" alt="Favicon">
                                                @endif
                                            </div>
                                        </div>
                                        <div>
                                            <input type="file" name="favicon" id="favicon-input" class="d-none" accept="image/*">
                                            <input type="hidden" name="remove_favicon" id="remove-favicon" value="0">
                                            <button type="button" class="btn btn-sm btn-soft-primary waves-effect mb-1" onclick="document.getElementById('favicon-input').click()">
                                                <i class="mdi mdi-upload"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-soft-danger waves-effect {{ $settings['favicon_path'] ? '' : 'd-none' }}" id="btn-remove-favicon">
                                                <i class="mdi mdi-trash-can"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mt-2 text-muted font-size-12 ps-md-4">Rekomendasi ICO/PNG 32x32px.</div>
                                </div>
                            </div>

                            <hr class="my-4 opacity-50">

                            <div class="mb-3">
                                <label class="form-label text-dark fw-bold">Nama Situs <span class="text-danger">*</span></label>
                                <input type="text" name="site_name" class="form-control @error('site_name') is-invalid @enderror" value="{{ old('site_name', $settings['site_name'] ?? '') }}" placeholder="Contoh: Philo Photo Booth" required>
                                @error('site_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-dark fw-bold">Deskripsi Situs</label>
                                <textarea name="site_description" class="form-control" rows="3" placeholder="Deskripsi singkat mengenai situs Anda...">{{ old('site_description', $settings['site_description'] ?? '') }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-dark fw-bold">Footer Text</label>
                                <input type="text" name="footer_text" class="form-control" value="{{ old('footer_text', $settings['footer_text'] ?? '') }}" placeholder="Contoh: © 2024 Philo. All rights reserved.">
                            </div>
                        </div>
                    </div>

                    <!-- Social Media -->
                    <div class="card mb-4 border-0 shadow-sm text-dark">
                        <div class="card-header bg-transparent border-bottom p-3">
                            <h5 class="card-title mb-0">Sosial Media & Kontak</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold"><i class="mdi mdi-instagram me-1 text-danger"></i> Instagram URL</label>
                                    <input type="url" name="instagram_url" class="form-control bg-light border-0" placeholder="https://instagram.com/account" value="{{ old('instagram_url', $settings['instagram_url'] ?? '') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold"><i class="mdi mdi-facebook me-1 text-primary"></i> Facebook URL</label>
                                    <input type="url" name="facebook_url" class="form-control bg-light border-0" placeholder="https://facebook.com/halaman-anda" value="{{ old('facebook_url', $settings['facebook_url'] ?? '') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold"><i class="mdi mdi-close me-1 text-dark"></i> X / Twitter URL</label>
                                    <input type="url" name="x_url" class="form-control bg-light border-0" placeholder="https://x.com/account" value="{{ old('x_url', $settings['x_url'] ?? '') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold"><i class="mdi mdi-music-note me-1 text-dark"></i> TikTok URL</label>
                                    <input type="url" name="tiktok_url" class="form-control bg-light border-0" placeholder="https://tiktok.com/@account" value="{{ old('tiktok_url', $settings['tiktok_url'] ?? '') }}">
                                </div>
                                <div class="col-md-12 mb-0">
                                    <label class="form-label fw-bold"><i class="mdi mdi-whatsapp me-1 text-success"></i> WhatsApp Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-0 text-muted">+62</span>
                                        <input type="text" name="whatsapp_number" class="form-control bg-light border-0" placeholder="81234567890" value="{{ old('whatsapp_number', $settings['whatsapp_number'] ?? '') }}">
                                    </div>
                                    <small class="text-muted mt-1 d-block">Gunakan format angka tanpa spasi (contoh: 81222334455).</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4 border-0 shadow-sm text-dark">
                        <div class="card-header bg-transparent border-bottom p-3">
                            <h5 class="card-title mb-0">Google Maps Embed</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Kode Embed / Iframe Google Maps</label>
                                <textarea
                                    name="google_maps_embed"
                                    id="google_maps_embed"
                                    class="form-control @error('google_maps_embed') is-invalid @enderror"
                                    rows="6"
                                    placeholder='Tempel iframe Google Maps di sini, contoh: <iframe src="..."></iframe>'
                                >{{ old('google_maps_embed', $settings['google_maps_embed'] ?? '') }}</textarea>
                                @error('google_maps_embed') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted mt-2 d-block">Buka Google Maps, pilih Share, lalu Embed a map, kemudian tempel kode iframe di sini.</small>
                            </div>

                            <div class="rounded-3 border bg-light overflow-hidden">
                                <div class="px-3 py-2 border-bottom bg-white text-muted small fw-semibold">
                                    Preview Embed
                                </div>
                                <div id="google-maps-preview" class="ratio ratio-16x9">
                                    @if(!empty($settings['google_maps_embed']))
                                        {!! $settings['google_maps_embed'] !!}
                                    @else
                                        <div class="d-flex align-items-center justify-content-center text-center text-muted px-4">
                                            Tempel kode embed Google Maps untuk melihat preview di sini.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Booth Settings -->
                    <div class="card mb-4 border-0 shadow-sm border-top border-primary border-4">
                        <div class="card-header bg-transparent border-bottom p-3">
                            <h5 class="card-title mb-0 text-primary fw-bold"><i class="mdi mdi-camera-burst me-1"></i> Pengaturan Booth</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-4">
                                <label class="form-label text-dark fw-bold">Countdown Sesi Foto</label>
                                <div class="input-group">
                                    <input type="number" name="booth_countdown_seconds" class="form-control" value="{{ old('booth_countdown_seconds', $settings['booth_countdown_seconds'] ?? 5) }}" min="1" max="10">
                                    <span class="input-group-text">Detik</span>
                                </div>
                                <div class="form-text font-size-11">Jeda sebelum kamera mengambil foto.</div>
                            </div>
                            <div class="mb-0">
                                <label class="form-label text-dark fw-bold">Idle Timeout</label>
                                <div class="input-group">
                                    <input type="number" name="booth_idle_timeout_seconds" class="form-control" value="{{ old('booth_idle_timeout_seconds', $settings['booth_idle_timeout_seconds'] ?? 60) }}" min="10" max="300">
                                    <span class="input-group-text">Detik</span>
                                </div>
                                <div class="form-text font-size-11">Waktu tunggu sebelum kembali ke layar awal.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Print Settings -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-transparent border-bottom p-3 d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0 text-dark fw-bold">Pengaturan Cetak</h5>
                            <div class="form-check form-switch form-switch-md">
                                <input class="form-check-input" type="checkbox" name="print_enabled" id="print_enabled" value="1" {{ (old('print_enabled', $settings['print_enabled'] ?? '0') == '1') ? 'checked' : '' }}>
                            </div>
                        </div>
                        <div id="print-settings-body" class="card-body p-4 {{ (old('print_enabled', $settings['print_enabled'] ?? '0') == '1') ? '' : 'opacity-40 pointer-events-none' }}">
                            <div class="mb-4">
                                <label class="form-label text-dark fw-bold">Kualitas Cetak (DPI)</label>
                                <select name="print_dpi" class="form-select bg-light border-0">
                                    <option value="72" {{ (old('print_dpi', $settings['print_dpi'] ?? '') == '72') ? 'selected' : '' }}>72 DPI (Cepat)</option>
                                    <option value="150" {{ (old('print_dpi', $settings['print_dpi'] ?? '') == '150') ? 'selected' : '' }}>150 DPI (Standar)</option>
                                    <option value="300" {{ (old('print_dpi', $settings['print_dpi'] ?? '300') == '300') ? 'selected' : '' }}>300 DPI (Terbaik)</option>
                                </select>
                            </div>
                            <div class="mb-0">
                                <label class="form-label text-dark fw-bold">Ukuran Kertas Default</label>
                                <select name="print_default_size" class="form-select bg-light border-0">
                                    <option value="strip" {{ (old('print_default_size', $settings['print_default_size'] ?? '') == 'strip') ? 'selected' : '' }}>Photo Strip (2x6")</option>
                                    <option value="A4" {{ (old('print_default_size', $settings['print_default_size'] ?? '') == 'A4') ? 'selected' : '' }}>A4</option>
                                    <option value="A3" {{ (old('print_default_size', $settings['print_default_size'] ?? '') == 'A3') ? 'selected' : '' }}>A3</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary btn-lg w-100 waves-effect waves-light p-3 shadow-sm rounded-3">
                            <i class="mdi mdi-content-save-check-outline me-1"></i> Simpan Pengaturan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    .pointer-events-none { pointer-events: none; }
    .opacity-40 { opacity: 0.4; }
    .form-switch-md .form-check-input { width: 45px; height: 22px; cursor: pointer; }
</style>
@endsection

@push('scripts')
<script>
    $(function() {
        // Print Settings Toggle
        $('#print_enabled').on('change', function() {
            if ($(this).is(':checked')) {
                $('#print-settings-body').removeClass('opacity-40 pointer-events-none');
            } else {
                $('#print-settings-body').addClass('opacity-40 pointer-events-none');
            }
        });

        // Logo Upload Preview
        $('#logo-input').on('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#logo-preview').attr('src', e.target.result).removeClass('d-none');
                    $('#logo-placeholder').addClass('d-none');
                    $('#remove-logo').val('0');
                    $('#btn-remove-logo').removeClass('d-none');
                }
                reader.readAsDataURL(file);
            }
        });

        // Remove Logo
        $('#btn-remove-logo').on('click', function() {
            $('#logo-preview').attr('src', '').addClass('d-none');
            $('#logo-placeholder').removeClass('d-none');
            $('#logo-input').val('');
            $('#remove-logo').val('1');
            $(this).addClass('d-none');
        });

        // Favicon Upload Preview
        $('#favicon-input').on('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#favicon-preview').attr('src', e.target.result).removeClass('d-none');
                    $('#favicon-placeholder').addClass('d-none');
                    $('#remove-favicon').val('0');
                    $('#btn-remove-favicon').removeClass('d-none');
                }
                reader.readAsDataURL(file);
            }
        });

        // Remove Favicon
        $('#btn-remove-favicon').on('click', function() {
            $('#favicon-preview').attr('src', '').addClass('d-none');
            $('#favicon-placeholder').removeClass('d-none');
            $('#favicon-input').val('');
            $('#remove-favicon').val('1');
            $(this).addClass('d-none');
        });

        $('#google_maps_embed').on('input', function() {
            const value = $(this).val().trim();
            const preview = $('#google-maps-preview');

            if (!value) {
                preview.html('<div class="d-flex align-items-center justify-content-center text-center text-muted px-4">Tempel kode embed Google Maps untuk melihat preview di sini.</div>');
                return;
            }

            preview.html(value);
        });
    });
</script>
@endpush
