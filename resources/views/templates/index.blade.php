@extends('layouts.admin')

@section('title', 'Frame Foto')
@section('page-title', 'Frame Foto')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Frame Foto</li>
@endsection

@section('content')
@php
    $sizeIcons = [
        'strip' => 'far fa-id-card',
        '4R' => 'far fa-image',
        'A4' => 'far fa-file-alt',
        'A3' => 'far fa-file',
    ];
    $sizeLabels = [
        'strip' => 'Photo Strip',
        '4R' => '4R',
        'A4' => 'A4',
        'A3' => 'A3',
    ];
    $sizeOrder = ['strip', '4R', 'A4', 'A3'];
@endphp

{{-- ─────────── Hero ─────────── --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="tpl-hero">
            <div class="tpl-hero-content">
                <div class="tpl-hero-icon">
                    <i class="far fa-images"></i>
                </div>
                <div>
                    <h4 class="mb-1">Frame Foto</h4>
                    <p class="mb-0 text-muted">Kelola frame yang dipilih pelanggan saat sesi foto. Setiap frame memiliki ukuran cetak dan jumlah slot foto sendiri.</p>
                </div>
            </div>
            <a href="{{ route('templates.builder') }}" class="btn btn-primary btn-lg tpl-hero-cta">
                <i class="fas fa-plus-circle me-1"></i> Tambah Frame
            </a>
        </div>
    </div>
</div>

{{-- ─────────── Filter & Search ─────────── --}}
<div class="row mb-3">
    <div class="col-12">
        <form method="GET" action="{{ route('templates.index') }}" class="tpl-filter-bar">
            <div class="tpl-search">
                <i class="fas fa-search"></i>
                <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Cari nama frame..." class="form-control border-0">
                @if(request()->hasAny(['q', 'status', 'size']))
                    <a href="{{ route('templates.index') }}" class="btn-clear" title="Reset filter">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
                <button type="submit" class="btn-search-submit">
                    <i class="fas fa-search"></i>
                </button>
            </div>

            <div class="tpl-chips">
                <a href="{{ route('templates.index', array_merge(request()->except(['status', 'page']))) }}"
                   class="chip {{ ! $filters['status'] ? 'chip-active' : '' }}">
                    Semua <span class="chip-count">{{ $counts['all'] }}</span>
                </a>
                <a href="{{ route('templates.index', array_merge(request()->except(['status', 'page']), ['status' => 'active'])) }}"
                   class="chip {{ $filters['status'] === 'active' ? 'chip-active' : '' }}">
                    <span class="chip-dot chip-dot-active"></span> Aktif <span class="chip-count">{{ $counts['active'] }}</span>
                </a>
                <a href="{{ route('templates.index', array_merge(request()->except(['status', 'page']), ['status' => 'inactive'])) }}"
                   class="chip {{ $filters['status'] === 'inactive' ? 'chip-active' : '' }}">
                    <span class="chip-dot chip-dot-inactive"></span> Nonaktif <span class="chip-count">{{ $counts['inactive'] }}</span>
                </a>
            </div>

            <div class="tpl-chips tpl-chips-size">
                <a href="{{ route('templates.index', array_merge(request()->except(['size', 'page']))) }}"
                   class="chip chip-sm {{ ! $filters['size'] ? 'chip-active' : '' }}">
                    Semua Ukuran
                </a>
                @foreach($sizeOrder as $size)
                    @if(($counts['sizes'][$size] ?? 0) > 0)
                        <a href="{{ route('templates.index', array_merge(request()->except(['size', 'page']), ['size' => $size])) }}"
                           class="chip chip-sm {{ $filters['size'] === $size ? 'chip-active' : '' }}">
                            <i class="{{ $sizeIcons[$size] }} me-1"></i>{{ $sizeLabels[$size] }}
                            <span class="chip-count">{{ $counts['sizes'][$size] }}</span>
                        </a>
                    @endif
                @endforeach
            </div>
        </form>
    </div>
</div>

{{-- ─────────── Grid ─────────── --}}
@if($templates->isEmpty())
    <div class="row">
        <div class="col-12">
            <div class="tpl-empty">
                <div class="tpl-empty-icon">
                    <i class="far fa-images"></i>
                </div>
                @if(request()->hasAny(['q', 'status', 'size']))
                    <h5 class="mb-2">Frame tidak ditemukan</h5>
                    <p class="text-muted mb-3">Coba ubah filter atau kata pencarian.</p>
                    <a href="{{ route('templates.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-sync-alt me-1"></i> Reset Filter
                    </a>
                @else
                    <h5 class="mb-2">Belum ada frame</h5>
                    <p class="text-muted mb-3">Buat frame pertama untuk memulai. Pelanggan akan memilih frame ini saat sesi foto.</p>
                    <a href="{{ route('templates.builder') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus-circle me-1"></i> Buat Frame Pertama
                    </a>
                @endif
            </div>
        </div>
    </div>
@else
    <div class="row g-4">
        @foreach($templates as $template)
            @php
                $thumb = $template->thumbnail_path
                    ? Storage::url($template->thumbnail_path)
                    : ($template->frame_path ? Storage::url($template->frame_path) : null);
            @endphp
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="tpl-card {{ $template->is_active ? '' : 'tpl-card-inactive' }}">
                    {{-- Status pill --}}
                    <div class="tpl-card-status">
                        @if($template->is_active)
                            <span class="tpl-status-pill tpl-status-active"><span class="tpl-status-dot"></span> Aktif</span>
                        @else
                            <span class="tpl-status-pill tpl-status-inactive">Nonaktif</span>
                        @endif
                    </div>

                    {{-- Thumbnail --}}
                    <div class="tpl-card-thumb">
                        @if($thumb)
                            <img src="{{ $thumb }}" alt="{{ $template->name }}">
                        @else
                            <div class="tpl-thumb-placeholder">
                                <i class="far fa-image"></i>
                                <span>Tidak ada preview</span>
                            </div>
                        @endif
                    </div>

                    {{-- Body --}}
                    <div class="tpl-card-body">
                        <h5 class="tpl-card-name">{{ $template->name }}</h5>

                        <div class="tpl-card-specs">
                            <div class="tpl-spec">
                                <i class="{{ $sizeIcons[$template->print_size] ?? 'far fa-image' }}"></i>
                                <span>{{ $sizeLabels[$template->print_size] ?? strtoupper($template->print_size) }}</span>
                            </div>
                            <div class="tpl-spec">
                                <i class="fas fa-th"></i>
                                <span>{{ $template->photo_slots }} slot</span>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="tpl-card-foot">
                        <a href="{{ route('templates.show', $template) }}" class="tpl-action-btn" title="Detail">
                            <i class="far fa-eye"></i>
                        </a>
                        <a href="{{ route('templates.builder.edit', $template) }}" class="tpl-action-btn tpl-action-edit" title="Edit di builder">
                            <i class="fas fa-pen"></i>
                        </a>
                        <button type="button" class="tpl-action-btn tpl-action-delete btn-delete"
                                data-name="{{ $template->name }}"
                                data-url="{{ route('templates.destroy', $template) }}"
                                title="Hapus">
                            <i class="far fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ─────────── Pagination ─────────── --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="tpl-pagination">
                <div class="tpl-pagination-info">
                    Menampilkan {{ $templates->firstItem() }}–{{ $templates->lastItem() }} dari {{ $templates->total() }} frame
                </div>
                <div>
                    {{ $templates->onEachSide(1)->links() }}
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@push('styles')
<style>
    /* ── Hero ── */
    .tpl-hero {
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
    .tpl-hero-content {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex: 1;
        min-width: 280px;
    }
    .tpl-hero-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        background: linear-gradient(135deg, #fef9c3 0%, #fef08a 100%);
        color: #b45309;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }
    .tpl-hero h4 {
        font-weight: 700;
        letter-spacing: -0.02em;
        color: #18181b;
    }
    .tpl-hero-cta {
        background: #18181b !important;
        border: none !important;
        color: #fafaf5 !important;
        font-weight: 600;
        padding: 0.85rem 1.5rem !important;
        border-radius: 12px !important;
        font-size: 0.93rem;
        box-shadow: 0 6px 16px -6px rgba(24,24,27,0.4);
        transition: all 0.18s ease;
    }
    .tpl-hero-cta:hover {
        background: #27272a !important;
        transform: translateY(-1px);
        box-shadow: 0 10px 22px -6px rgba(24,24,27,0.5);
    }

    /* ── Filter bar ── */
    .tpl-filter-bar {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.05);
        border-radius: 14px;
        padding: 0.85rem 1rem;
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
    }
    .tpl-search {
        position: relative;
        flex: 1;
        min-width: 240px;
        display: flex;
        align-items: center;
        background: #fafaf5;
        border-radius: 10px;
        padding: 0 0.5rem 0 0.85rem;
    }
    .tpl-search > i.fas { color: #a1a1aa; font-size: 0.95rem; flex-shrink: 0; }
    .tpl-search input {
        background: transparent !important;
        padding: 0.5rem !important;
        font-size: 0.9rem !important;
        box-shadow: none !important;
    }
    .tpl-search input:focus { background: transparent !important; }
    .btn-clear {
        background: rgba(0,0,0,0.06);
        color: #71717a;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        flex-shrink: 0;
        transition: all 0.15s;
    }
    .btn-clear:hover { background: rgba(0,0,0,0.1); color: #18181b; }
    .btn-search-submit {
        background: #18181b;
        color: #fff;
        border: none;
        width: 30px;
        height: 30px;
        border-radius: 8px;
        margin-left: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.78rem;
        cursor: pointer;
        transition: background 0.15s;
    }
    .btn-search-submit:hover { background: #27272a; }

    .tpl-chips {
        display: flex;
        gap: 0.4rem;
        flex-wrap: wrap;
    }
    .tpl-chips-size { width: 100%; padding-top: 0.35rem; }
    .chip {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.45rem 0.85rem;
        border-radius: 999px;
        background: #fafaf5;
        color: #52525b;
        font-size: 0.82rem;
        font-weight: 500;
        text-decoration: none;
        border: 1px solid transparent;
        transition: all 0.15s;
    }
    .chip-sm { padding: 0.35rem 0.75rem; font-size: 0.78rem; }
    .chip:hover { background: #f4f4ed; color: #18181b; }
    .chip-active {
        background: #18181b;
        color: #fafaf5;
        font-weight: 600;
    }
    .chip-active:hover { background: #27272a; color: #fafaf5; }
    .chip-count {
        background: rgba(0,0,0,0.06);
        padding: 0.05rem 0.45rem;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }
    .chip-active .chip-count {
        background: rgba(255,255,255,0.18);
        color: #fff;
    }
    .chip-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }
    .chip-dot-active { background: #22c55e; box-shadow: 0 0 0 2px rgba(34,197,94,0.2); }
    .chip-dot-inactive { background: #d4d4d8; }

    /* ── Template card ── */
    .tpl-card {
        position: relative;
        background: #fff;
        border: 1px solid rgba(0,0,0,0.06);
        border-radius: 16px;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
        transition: all 0.2s ease;
    }
    .tpl-card:hover {
        border-color: rgba(0,0,0,0.12);
        box-shadow: 0 12px 28px -12px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }
    .tpl-card-inactive { opacity: 0.7; background: #fafaf5; }

    .tpl-card-status {
        position: absolute;
        top: 12px;
        right: 12px;
        z-index: 2;
    }
    .tpl-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.68rem;
        font-weight: 600;
        padding: 0.25rem 0.65rem;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        backdrop-filter: blur(6px);
    }
    .tpl-status-active {
        background: rgba(34,197,94,0.95);
        color: #fff;
    }
    .tpl-status-inactive {
        background: rgba(0,0,0,0.55);
        color: #fff;
    }
    .tpl-status-dot {
        width: 6px;
        height: 6px;
        background: #fff;
        border-radius: 50%;
    }

    .tpl-card-thumb {
        position: relative;
        background: linear-gradient(135deg, #f4f4f5 0%, #e4e4e7 100%);
        aspect-ratio: 1 / 1;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .tpl-card-thumb img {
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
        object-fit: contain;
        background: #fff;
    }
    .tpl-thumb-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        color: #a1a1aa;
        font-size: 0.78rem;
    }
    .tpl-thumb-placeholder i {
        font-size: 2.5rem;
    }

    .tpl-card-body {
        padding: 1rem 1.1rem 0.4rem;
    }
    .tpl-card-name {
        font-weight: 700;
        font-size: 1rem;
        color: #18181b;
        letter-spacing: -0.015em;
        margin-bottom: 0.5rem;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .tpl-card-specs {
        display: flex;
        gap: 0.45rem;
        flex-wrap: wrap;
    }
    .tpl-spec {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.3rem 0.65rem;
        background: #fafaf5;
        border-radius: 8px;
        font-size: 0.74rem;
        color: #52525b;
        font-weight: 600;
    }
    .tpl-spec i { color: #a1a1aa; font-size: 0.78rem; }

    .tpl-card-foot {
        display: flex;
        gap: 0.35rem;
        padding: 0.85rem 1.1rem 1.1rem;
        margin-top: auto;
    }
    .tpl-action-btn {
        flex: 1;
        height: 36px;
        border-radius: 9px;
        background: #fafaf5;
        color: #52525b;
        border: 1px solid transparent;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.15s;
        text-decoration: none;
        font-size: 0.9rem;
    }
    .tpl-action-btn:hover {
        background: #f4f4ed;
        color: #18181b;
        border-color: rgba(0,0,0,0.08);
    }
    .tpl-action-edit:hover {
        background: #fef9c3 !important;
        color: #b45309 !important;
        border-color: rgba(232,201,0,0.3);
    }
    .tpl-action-delete:hover {
        background: #fee2e2 !important;
        color: #b91c1c !important;
        border-color: rgba(239,68,68,0.2);
    }

    /* ── Empty ── */
    .tpl-empty {
        background: #fff;
        border: 2px dashed rgba(0,0,0,0.08);
        border-radius: 16px;
        padding: 3rem 1.5rem;
        text-align: center;
    }
    .tpl-empty-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 1.25rem;
        background: #fafaf5;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.4rem;
        color: #a1a1aa;
    }
    .tpl-empty h5 {
        color: #18181b;
        font-weight: 700;
    }

    /* ── Pagination ── */
    .tpl-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        background: #fff;
        border: 1px solid rgba(0,0,0,0.05);
        border-radius: 14px;
        padding: 0.85rem 1.1rem;
    }
    .tpl-pagination-info {
        font-size: 0.83rem;
        color: #71717a;
    }
    .tpl-pagination .pagination {
        margin: 0;
        gap: 0.25rem;
    }
    .tpl-pagination .page-link {
        border: 1px solid transparent;
        background: #fafaf5;
        color: #52525b;
        font-size: 0.85rem;
        font-weight: 600;
        border-radius: 8px !important;
        min-width: 34px;
        text-align: center;
        padding: 0.35rem 0.7rem;
    }
    .tpl-pagination .page-link:hover {
        background: #f4f4ed;
        color: #18181b;
    }
    .tpl-pagination .page-item.active .page-link {
        background: #18181b;
        color: #fff;
        border-color: #18181b;
    }
    .tpl-pagination .page-item.disabled .page-link {
        background: transparent;
        color: #d4d4d8;
    }
</style>
@endpush

@push('scripts')
<script>
$(function () {
    // Auto-submit search after typing pause
    var searchTimer;
    $('input[name="q"]').on('input', function() {
        clearTimeout(searchTimer);
        var form = this.form;
        searchTimer = setTimeout(function() { form.submit(); }, 450);
    });

    $('.btn-delete').on('click', function() {
        var name = $(this).data('name');
        var url = $(this).data('url');

        Swal.fire({
            title: 'Hapus Frame?',
            html: 'Frame <strong>' + name + '</strong> dan file-nya akan dihapus permanen.',
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
                        }).then(function() { location.reload(); });
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
