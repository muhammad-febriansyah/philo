@extends('layouts.admin')

@section('title', 'Detail Frame — ' . $template->name)
@section('page-title', 'Detail Frame')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('templates.index') }}">Frame Foto</a></li>
    <li class="breadcrumb-item active">{{ $template->name }}</li>
@endsection

@php
    $sizeIcons = [
        'strip' => 'far fa-id-card',
        '4R' => 'far fa-image',
        'A4' => 'far fa-file-alt',
        'A3' => 'far fa-file',
    ];
    $sizeLabels = [
        'strip' => 'Photo Strip (2"×6")',
        '4R' => '4R (4"×6")',
        'A4' => 'A4 (210×297mm)',
        'A3' => 'A3 (297×420mm)',
    ];
    $imgUrl = $template->thumbnail_url ?? $template->frame_url;
@endphp

@section('content')

{{-- ─────────── Header Card ─────────── --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="tpl-detail-header">
            <div class="tpl-detail-header-main">
                <div class="tpl-detail-icon">
                    <i class="far fa-images"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        @if($template->is_active)
                            <span class="tpl-status-pill tpl-status-active"><span class="tpl-status-dot"></span> Aktif</span>
                        @else
                            <span class="tpl-status-pill tpl-status-inactive">Nonaktif</span>
                        @endif
                        <span class="tpl-id-pill">ID #{{ $template->id }}</span>
                    </div>
                    <h4 class="tpl-detail-name mb-1">{{ $template->name }}</h4>
                    <p class="tpl-detail-sub mb-0">
                        Frame {{ $sizeLabels[$template->print_size] ?? strtoupper($template->print_size) }} dengan {{ $template->photo_slots }} slot foto
                    </p>
                </div>
            </div>
            <div class="tpl-detail-actions">
                <a href="{{ route('templates.builder.edit', $template) }}" class="btn btn-warning">
                    <i class="fas fa-pen me-1"></i> Edit di Builder
                </a>
                <button type="button" class="btn btn-outline-danger btn-delete"
                        data-name="{{ $template->name }}"
                        data-url="{{ route('templates.destroy', $template) }}">
                    <i class="far fa-trash-alt me-1"></i> Hapus
                </button>
                <a href="{{ route('templates.index') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- ─────────── Left: Preview ─────────── --}}
    <div class="col-xl-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-0">
                <div class="tpl-preview-header">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">Preview Frame</h6>
                        <p class="small text-muted mb-0">Tampilan frame yang akan digunakan saat sesi foto.</p>
                    </div>
                    @if($imgUrl)
                        <a href="{{ $imgUrl }}" target="_blank" rel="noopener" class="tpl-preview-link">
                            <i class="fas fa-external-link-alt me-1"></i> Buka asli
                        </a>
                    @endif
                </div>
                <div class="tpl-preview-stage">
                    @if($imgUrl)
                        <img src="{{ $imgUrl }}" alt="{{ $template->name }}" class="tpl-preview-img">
                    @else
                        <div class="tpl-preview-empty">
                            <i class="far fa-image"></i>
                            <p class="mb-0 mt-2">Belum ada gambar frame</p>
                            <a href="{{ route('templates.builder.edit', $template) }}" class="btn btn-sm btn-warning mt-3">
                                <i class="fas fa-upload me-1"></i> Upload Frame
                            </a>
                        </div>
                    @endif
                </div>
                <div class="tpl-preview-footer">
                    <span class="text-muted small">
                        <i class="fas fa-info-circle me-1"></i>
                        Setiap kotak kosong pada frame adalah lokasi slot foto pelanggan.
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- ─────────── Right: Specs + Stats ─────────── --}}
    <div class="col-xl-5">
        {{-- Spec cards --}}
        <div class="row g-3 mb-3">
            <div class="col-6">
                <div class="tpl-spec-card tpl-spec-yellow">
                    <div class="tpl-spec-icon"><i class="{{ $sizeIcons[$template->print_size] ?? 'far fa-image' }}"></i></div>
                    <div class="tpl-spec-label">Ukuran Cetak</div>
                    <div class="tpl-spec-value">{{ strtoupper($template->print_size) }}</div>
                    <div class="tpl-spec-sub">{{ $sizeLabels[$template->print_size] ?? '—' }}</div>
                </div>
            </div>
            <div class="col-6">
                <div class="tpl-spec-card tpl-spec-blue">
                    <div class="tpl-spec-icon"><i class="fas fa-th"></i></div>
                    <div class="tpl-spec-label">Slot Foto</div>
                    <div class="tpl-spec-value">{{ $template->photo_slots }}</div>
                    <div class="tpl-spec-sub">slot dalam frame</div>
                </div>
            </div>
            <div class="col-6">
                <div class="tpl-spec-card tpl-spec-green">
                    <div class="tpl-spec-icon"><i class="fas fa-camera"></i></div>
                    <div class="tpl-spec-label">Sesi Foto</div>
                    <div class="tpl-spec-value">{{ number_format($stats['sessions_total']) }}</div>
                    <div class="tpl-spec-sub">{{ number_format($stats['sessions_completed']) }} sesi selesai</div>
                </div>
            </div>
            <div class="col-6">
                <div class="tpl-spec-card tpl-spec-purple">
                    <div class="tpl-spec-icon"><i class="fas fa-tags"></i></div>
                    <div class="tpl-spec-label">Dipakai Paket</div>
                    <div class="tpl-spec-value">{{ number_format($stats['packages_count']) }}</div>
                    <div class="tpl-spec-sub">{{ $stats['packages_count'] === 0 ? 'Belum dipakai paket' : 'paket aktif' }}</div>
                </div>
            </div>
        </div>

        {{-- Used by packages --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body p-0">
                <div class="tpl-section-head">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-tags text-muted"></i>
                        <h6 class="mb-0 fw-bold">Paket yang Memakai</h6>
                    </div>
                    <span class="badge bg-light text-dark">{{ $stats['packages_count'] }}</span>
                </div>
                @if($template->packages->isEmpty())
                    <div class="tpl-empty-block">
                        <i class="fas fa-info-circle text-muted"></i>
                        <p class="text-muted small mb-2 mt-2">Belum ada paket yang memakai frame ini.</p>
                        <a href="{{ route('packages.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-right me-1"></i> Kelola Paket
                        </a>
                    </div>
                @else
                    <ul class="list-unstyled mb-0">
                        @foreach($template->packages as $pkg)
                            <li class="tpl-pkg-row">
                                <div class="d-flex align-items-center gap-3 flex-grow-1">
                                    <div class="tpl-pkg-icon">
                                        <i class="fas fa-tag"></i>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <span class="fw-semibold text-dark">{{ $pkg->name }}</span>
                                            @if(! $pkg->is_active)
                                                <span class="badge bg-light text-muted small">Nonaktif</span>
                                            @endif
                                        </div>
                                        <div class="small text-muted">
                                            Rp {{ number_format($pkg->price, 0, ',', '.') }} · {{ $pkg->print_copies }} lembar
                                        </div>
                                    </div>
                                </div>
                                <a href="{{ route('packages.show', $pkg) }}" class="btn btn-sm btn-light" title="Buka paket">
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        {{-- System info --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="fas fa-info-circle text-muted"></i>
                    <h6 class="mb-0 fw-bold">Informasi Sistem</h6>
                </div>
                <dl class="tpl-meta-list mb-0">
                    <dt>ID Frame</dt>
                    <dd>#{{ $template->id }}</dd>
                    <dt>Dibuat</dt>
                    <dd>{{ $template->created_at->format('d M Y, H:i') }} <span class="text-muted">({{ $template->created_at->diffForHumans() }})</span></dd>
                    <dt>Diperbarui</dt>
                    <dd>{{ $template->updated_at->format('d M Y, H:i') }} <span class="text-muted">({{ $template->updated_at->diffForHumans() }})</span></dd>
                    @if($stats['last_used_at'])
                        <dt>Terakhir Dipakai</dt>
                        <dd>{{ \Carbon\Carbon::parse($stats['last_used_at'])->format('d M Y, H:i') }} <span class="text-muted">({{ \Carbon\Carbon::parse($stats['last_used_at'])->diffForHumans() }})</span></dd>
                    @endif
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* ── Header ── */
    .tpl-detail-header {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.05);
        border-radius: 16px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
        flex-wrap: wrap;
        box-shadow: 0 2px 6px rgba(0,0,0,0.03);
    }
    .tpl-detail-header-main {
        display: flex;
        align-items: center;
        gap: 1.1rem;
        flex: 1;
        min-width: 280px;
    }
    .tpl-detail-icon {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        background: linear-gradient(135deg, #fef9c3 0%, #fef08a 100%);
        color: #b45309;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.7rem;
        flex-shrink: 0;
    }
    .tpl-detail-name {
        font-weight: 700;
        font-size: 1.45rem;
        color: #18181b;
        letter-spacing: -0.02em;
    }
    .tpl-detail-sub {
        color: #71717a;
        font-size: 0.88rem;
    }
    .tpl-detail-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    /* ── Status pill ── */
    .tpl-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.7rem;
        font-weight: 600;
        padding: 0.3rem 0.7rem;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .tpl-status-active { background: rgba(34,197,94,0.12); color: #15803d; }
    .tpl-status-inactive { background: rgba(0,0,0,0.05); color: #71717a; }
    .tpl-status-dot {
        width: 6px;
        height: 6px;
        background: #22c55e;
        border-radius: 50%;
        box-shadow: 0 0 0 2px rgba(34,197,94,0.18);
    }
    .tpl-id-pill {
        font-family: 'SFMono-Regular', Menlo, monospace;
        font-size: 0.7rem;
        font-weight: 600;
        background: rgba(0,0,0,0.05);
        color: #52525b;
        padding: 0.25rem 0.55rem;
        border-radius: 6px;
    }

    /* ── Preview ── */
    .tpl-preview-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.1rem 1.4rem;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    .tpl-preview-link {
        font-size: 0.78rem;
        color: #71717a;
        text-decoration: none;
        padding: 0.3rem 0.65rem;
        background: #fafaf5;
        border-radius: 8px;
        transition: all 0.15s;
    }
    .tpl-preview-link:hover {
        background: #f4f4ed;
        color: #18181b;
    }
    .tpl-preview-stage {
        background:
            linear-gradient(45deg, #f4f4f5 25%, transparent 25%, transparent 75%, #f4f4f5 75%, #f4f4f5),
            linear-gradient(45deg, #f4f4f5 25%, transparent 25%, transparent 75%, #f4f4f5 75%, #f4f4f5);
        background-size: 18px 18px;
        background-position: 0 0, 9px 9px;
        background-color: #fafafa;
        padding: 1.5rem;
        min-height: 380px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .tpl-preview-img {
        max-width: 100%;
        max-height: 440px;
        width: auto;
        height: auto;
        object-fit: contain;
        background: #fff;
        box-shadow: 0 6px 22px rgba(0,0,0,0.08), 0 2px 6px rgba(0,0,0,0.04);
        border-radius: 4px;
    }
    .tpl-preview-empty {
        text-align: center;
        color: #a1a1aa;
    }
    .tpl-preview-empty i {
        font-size: 3.5rem;
        opacity: 0.3;
    }
    .tpl-preview-footer {
        padding: 0.85rem 1.4rem;
        border-top: 1px solid rgba(0,0,0,0.05);
        background: #fafaf5;
    }

    /* ── Spec cards ── */
    .tpl-spec-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.06);
        border-radius: 14px;
        padding: 1.1rem;
        height: 100%;
        position: relative;
        transition: all 0.18s ease;
    }
    .tpl-spec-card::before {
        content: '';
        position: absolute;
        inset: 0 0 auto 0;
        height: 3px;
        border-radius: 14px 14px 0 0;
        background: var(--spec-color);
    }
    .tpl-spec-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
    }
    .tpl-spec-yellow { --spec-color: #f1b44c; }
    .tpl-spec-blue { --spec-color: #50a5f1; }
    .tpl-spec-green { --spec-color: #34c38f; }
    .tpl-spec-purple { --spec-color: #8a5cf0; }

    .tpl-spec-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: rgba(0,0,0,0.04);
        color: var(--spec-color);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        margin-bottom: 0.6rem;
    }
    .tpl-spec-label {
        font-size: 0.7rem;
        color: #a1a1aa;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }
    .tpl-spec-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #18181b;
        line-height: 1.1;
        margin: 0.2rem 0;
        letter-spacing: -0.02em;
    }
    .tpl-spec-sub {
        font-size: 0.74rem;
        color: #71717a;
    }

    /* ── Section head ── */
    .tpl-section-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.3rem;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    /* ── Package list ── */
    .tpl-pkg-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.85rem 1.3rem;
        border-bottom: 1px solid rgba(0,0,0,0.04);
        transition: background 0.12s;
    }
    .tpl-pkg-row:last-child { border-bottom: none; }
    .tpl-pkg-row:hover { background: #fafaf5; }
    .tpl-pkg-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: linear-gradient(135deg, #fef9c3 0%, #fef08a 100%);
        color: #b45309;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        flex-shrink: 0;
    }

    /* ── Empty block ── */
    .tpl-empty-block {
        text-align: center;
        padding: 1.5rem 1rem;
    }
    .tpl-empty-block i {
        font-size: 1.5rem;
        opacity: 0.5;
    }

    /* ── Meta list ── */
    .tpl-meta-list {
        display: grid;
        grid-template-columns: 130px 1fr;
        gap: 0.65rem 1rem;
    }
    .tpl-meta-list dt {
        font-size: 0.78rem;
        color: #71717a;
        font-weight: 500;
        margin: 0;
    }
    .tpl-meta-list dd {
        font-size: 0.85rem;
        color: #18181b;
        font-weight: 600;
        margin: 0;
    }

    .min-w-0 { min-width: 0; }

    @media (max-width: 1199.98px) {
        .tpl-spec-value { font-size: 1.3rem; }
    }
</style>
@endpush

@push('scripts')
<script>
$(function () {
    $('.btn-delete').on('click', function() {
        var name = $(this).data('name');
        var url = $(this).data('url');

        Swal.fire({
            title: 'Hapus Frame?',
            html: 'Frame <strong>' + name + '</strong> dan file-nya akan dihapus permanen.<br>Tindakan ini tidak bisa dibatalkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#71717a',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
        }).then(function(result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(function() {
                            window.location.href = '{{ route("templates.index") }}';
                        });
                    },
                    error: function() {
                        Swal.fire({ icon: 'error', title: 'Gagal!', text: 'Terjadi kesalahan saat menghapus.' });
                    }
                });
            }
        });
    });
});
</script>
@endpush
