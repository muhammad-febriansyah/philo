@extends('layouts.admin')

@section('title', 'Paket Foto')
@section('page-title', 'Paket Foto')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Paket Foto</li>
@endsection

@section('content')
@php
    $totalActive = $packages->where('is_active', true)->count();
    $totalInactive = $packages->where('is_active', false)->count();
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
@endphp

{{-- ─────────── Quick Help / Empty State ─────────── --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="pkg-hero">
            <div class="pkg-hero-content">
                <div class="pkg-hero-icon">
                    <i class="fas fa-tags"></i>
                </div>
                <div>
                    <h4 class="mb-1">Paket Foto</h4>
                    <p class="mb-0 text-muted">Atur paket harga yang dipilih pelanggan saat memulai sesi foto. Tiap paket terdiri dari: jumlah foto, ukuran cetak, jumlah lembar, dan harga.</p>
                </div>
            </div>
            <a href="{{ route('packages.create') }}" class="btn btn-primary btn-lg pkg-hero-cta">
                <i class="fas fa-plus-circle me-1"></i> Tambah Paket Baru
            </a>
        </div>
    </div>
</div>

{{-- ─────────── Filters & Search ─────────── --}}
<div class="row mb-3">
    <div class="col-12">
        <form method="GET" action="{{ route('packages.index') }}" class="pkg-filter-bar">
            <div class="pkg-search">
                <i class="fas fa-search"></i>
                <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Cari nama paket..." class="form-control border-0">
                @if(request()->hasAny(['q', 'status', 'size']))
                    <a href="{{ route('packages.index') }}" class="btn-clear" title="Reset filter">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </div>

            <div class="pkg-chips">
                <a href="{{ route('packages.index', array_merge(request()->except('status'))) }}"
                   class="chip {{ ! $filters['status'] ? 'chip-active' : '' }}">
                    Semua <span class="chip-count">{{ $packages->count() }}</span>
                </a>
                <a href="{{ route('packages.index', array_merge(request()->except('status'), ['status' => 'active'])) }}"
                   class="chip {{ (string) $filters['status'] === 'active' ? 'chip-active' : '' }}">
                    <span class="chip-dot chip-dot-active"></span> Aktif <span class="chip-count">{{ $totalActive }}</span>
                </a>
                <a href="{{ route('packages.index', array_merge(request()->except('status'), ['status' => 'inactive'])) }}"
                   class="chip {{ (string) $filters['status'] === 'inactive' ? 'chip-active' : '' }}">
                    <span class="chip-dot chip-dot-inactive"></span> Nonaktif <span class="chip-count">{{ $totalInactive }}</span>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ─────────── Package Cards Grid ─────────── --}}
@if($packages->isEmpty())
    <div class="row">
        <div class="col-12">
            <div class="pkg-empty">
                <div class="pkg-empty-icon">
                    <i class="fas fa-tags"></i>
                </div>
                @if(request()->hasAny(['q', 'status', 'size']))
                    <h5 class="mb-2">Paket tidak ditemukan</h5>
                    <p class="text-muted mb-3">Coba ubah filter atau kata pencarian.</p>
                    <a href="{{ route('packages.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-sync-alt me-1"></i> Reset Filter
                    </a>
                @else
                    <h5 class="mb-2">Belum ada paket foto</h5>
                    <p class="text-muted mb-3">Buat paket pertama untuk memulai. Pelanggan akan memilih paket ini saat sesi foto.</p>
                    <a href="{{ route('packages.create') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus-circle me-1"></i> Buat Paket Pertama
                    </a>
                @endif
            </div>
        </div>
    </div>
@else
    <div class="row g-4">
        @foreach($packages as $package)
            <div class="col-xl-4 col-md-6">
                <div class="pkg-card {{ $package->is_active ? '' : 'pkg-card-inactive' }}">
                    {{-- Status ribbon --}}
                    <div class="pkg-card-status">
                        @if($package->is_active)
                            <span class="pkg-status-pill pkg-status-active">
                                <span class="pkg-status-dot"></span> Aktif
                            </span>
                        @else
                            <span class="pkg-status-pill pkg-status-inactive">Nonaktif</span>
                        @endif
                    </div>

                    {{-- Header: Name + Description --}}
                    <div class="pkg-card-head">
                        <h5 class="pkg-card-name">{{ $package->name }}</h5>
                        @if($package->description)
                            <p class="pkg-card-desc">{{ \Illuminate\Support\Str::limit($package->description, 70) }}</p>
                        @else
                            <p class="pkg-card-desc text-muted fst-italic">Tanpa deskripsi</p>
                        @endif
                    </div>

                    {{-- Big price --}}
                    <div class="pkg-card-price">
                        <span class="pkg-price-currency">Rp</span>
                        <span class="pkg-price-amount">{{ number_format($package->price, 0, ',', '.') }}</span>
                    </div>

                    {{-- Specs grid (derived from templates) --}}
                    @php
                        $firstTemplate = $package->templates->first();
                        $templatePhotoCount = $firstTemplate?->photo_slots ?? $package->photo_count ?? 0;
                        $templateSize = $firstTemplate?->print_size ?? $package->print_size ?? '';
                    @endphp
                    <div class="pkg-card-specs">
                        <div class="pkg-spec">
                            <div class="pkg-spec-icon">
                                <i class="fas fa-camera"></i>
                            </div>
                            <div class="pkg-spec-body">
                                <div class="pkg-spec-label">Foto diambil</div>
                                <div class="pkg-spec-value">{{ $templatePhotoCount > 0 ? $templatePhotoCount.' foto' : '—' }}</div>
                            </div>
                        </div>
                        <div class="pkg-spec">
                            <div class="pkg-spec-icon">
                                <i class="{{ $sizeIcons[$templateSize] ?? 'far fa-image' }}"></i>
                            </div>
                            <div class="pkg-spec-body">
                                <div class="pkg-spec-label">Ukuran cetak</div>
                                <div class="pkg-spec-value">{{ $templateSize ? ($sizeLabels[$templateSize] ?? strtoupper($templateSize)) : '—' }}</div>
                            </div>
                        </div>
                        <div class="pkg-spec">
                            <div class="pkg-spec-icon">
                                <i class="fas fa-print"></i>
                            </div>
                            <div class="pkg-spec-body">
                                <div class="pkg-spec-label">Lembar cetak</div>
                                <div class="pkg-spec-value">{{ $package->print_copies }} lembar</div>
                            </div>
                        </div>
                        <div class="pkg-spec">
                            <div class="pkg-spec-icon">
                                <i class="far fa-images"></i>
                            </div>
                            <div class="pkg-spec-body">
                                <div class="pkg-spec-label">Template</div>
                                <div class="pkg-spec-value">
                                    {{ $package->templates_count }} template
                                    @if($package->templates_count === 0)
                                        <i class="fas fa-exclamation-circle text-warning ms-1" title="Belum ada template"></i>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Footer: meta + actions --}}
                    <div class="pkg-card-foot">
                        <div class="pkg-card-meta">
                            <i class="fas fa-money-bill-wave"></i>
                            {{ number_format($package->paid_transactions_count) }} transaksi terbayar
                        </div>
                        <div class="pkg-card-actions">
                            <a href="{{ route('packages.show', $package) }}" class="pkg-action-btn" title="Detail">
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('packages.edit', $package) }}" class="pkg-action-btn pkg-action-edit" title="Edit">
                                <i class="fas fa-pen"></i>
                            </a>
                            <button type="button" class="pkg-action-btn pkg-action-delete btn-delete"
                                    data-name="{{ $package->name }}"
                                    data-url="{{ route('packages.destroy', $package) }}"
                                    title="Hapus">
                                <i class="far fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

@endsection

@push('styles')
<style>
    /* ── Hero ── */
    .pkg-hero {
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
    .pkg-hero-content {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex: 1;
        min-width: 280px;
    }
    .pkg-hero-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        background: linear-gradient(135deg, #fef9c3 0%, #fef08a 100%);
        color: #b45309;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        flex-shrink: 0;
    }
    .pkg-hero h4 {
        font-weight: 700;
        letter-spacing: -0.02em;
        color: #18181b;
    }
    .pkg-hero-cta {
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
    .pkg-hero-cta:hover {
        background: #27272a !important;
        transform: translateY(-1px);
        box-shadow: 0 10px 22px -6px rgba(24,24,27,0.5);
    }

    /* ── Filter bar ── */
    .pkg-filter-bar {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.05);
        border-radius: 14px;
        padding: 0.85rem 1rem;
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
    }
    .pkg-search {
        position: relative;
        flex: 1;
        min-width: 240px;
        display: flex;
        align-items: center;
        background: #fafaf5;
        border-radius: 10px;
        padding: 0 0.5rem 0 0.85rem;
    }
    .pkg-search .mdi-magnify {
        color: #a1a1aa;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    .pkg-search input {
        background: transparent !important;
        padding: 0.5rem 0.5rem !important;
        font-size: 0.9rem !important;
        box-shadow: none !important;
    }
    .pkg-search input:focus { background: transparent !important; }
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
    .pkg-chips {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
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
    .chip:hover {
        background: #f4f4ed;
        color: #18181b;
    }
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

    /* ── Package card ── */
    .pkg-card {
        position: relative;
        background: #fff;
        border: 1px solid rgba(0,0,0,0.06);
        border-radius: 18px;
        padding: 1.5rem;
        height: 100%;
        display: flex;
        flex-direction: column;
        gap: 1.2rem;
        transition: all 0.2s ease;
        overflow: hidden;
    }
    .pkg-card:hover {
        border-color: rgba(0,0,0,0.12);
        box-shadow: 0 12px 28px -12px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }
    .pkg-card-inactive {
        opacity: 0.7;
        background: #fafaf5;
    }
    .pkg-card-inactive .pkg-card-price { color: #71717a; }

    .pkg-card-status {
        position: absolute;
        top: 1.25rem;
        right: 1.25rem;
        z-index: 2;
    }
    .pkg-status-pill {
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
    .pkg-status-active {
        background: rgba(34,197,94,0.12);
        color: #15803d;
    }
    .pkg-status-inactive {
        background: rgba(0,0,0,0.05);
        color: #71717a;
    }
    .pkg-status-dot {
        width: 6px;
        height: 6px;
        background: #22c55e;
        border-radius: 50%;
        box-shadow: 0 0 0 2px rgba(34,197,94,0.18);
    }

    .pkg-card-head {
        padding-right: 4.5rem;
    }
    .pkg-card-name {
        font-weight: 700;
        font-size: 1.15rem;
        color: #18181b;
        letter-spacing: -0.02em;
        margin-bottom: 0.35rem;
    }
    .pkg-card-desc {
        font-size: 0.83rem;
        color: #71717a;
        margin-bottom: 0;
        line-height: 1.45;
    }

    .pkg-card-price {
        display: flex;
        align-items: baseline;
        gap: 0.25rem;
        padding: 0.25rem 0;
        border-bottom: 1px dashed rgba(0,0,0,0.08);
        padding-bottom: 1.1rem;
        color: #18181b;
    }
    .pkg-price-currency {
        font-size: 0.95rem;
        font-weight: 600;
        color: #71717a;
    }
    .pkg-price-amount {
        font-size: 2rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        font-variant-numeric: tabular-nums;
        line-height: 1;
    }

    .pkg-card-specs {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem 1rem;
    }
    .pkg-spec {
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }
    .pkg-spec-icon {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        background: #fef9c3;
        color: #b45309;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .pkg-spec-body { min-width: 0; }
    .pkg-spec-label {
        font-size: 0.68rem;
        color: #a1a1aa;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        font-weight: 500;
        line-height: 1.1;
    }
    .pkg-spec-value {
        font-size: 0.85rem;
        font-weight: 600;
        color: #18181b;
        margin-top: 1px;
    }

    .pkg-card-foot {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(0,0,0,0.05);
        margin-top: auto;
    }
    .pkg-card-meta {
        font-size: 0.78rem;
        color: #71717a;
        display: flex;
        align-items: center;
        gap: 0.35rem;
        font-weight: 500;
    }
    .pkg-card-meta .mdi { color: #a1a1aa; }
    .pkg-card-actions {
        display: flex;
        gap: 0.35rem;
    }
    .pkg-action-btn {
        width: 34px;
        height: 34px;
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
        font-size: 1rem;
    }
    .pkg-action-btn:hover {
        background: #f4f4ed;
        color: #18181b;
        border-color: rgba(0,0,0,0.08);
    }
    .pkg-action-edit:hover {
        background: #fef9c3 !important;
        color: #b45309 !important;
        border-color: rgba(232,201,0,0.3);
    }
    .pkg-action-delete:hover {
        background: #fee2e2 !important;
        color: #b91c1c !important;
        border-color: rgba(239,68,68,0.2);
    }

    /* ── Empty state ── */
    .pkg-empty {
        background: #fff;
        border: 2px dashed rgba(0,0,0,0.08);
        border-radius: 16px;
        padding: 3rem 1.5rem;
        text-align: center;
    }
    .pkg-empty-icon {
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
    .pkg-empty h5 {
        color: #18181b;
        font-weight: 700;
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
            title: 'Hapus Paket?',
            html: 'Paket <strong>' + name + '</strong> akan dihapus secara permanen.<br>Tindakan ini tidak bisa dibatalkan.',
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
                        Swal.fire({ icon: 'error', title: 'Gagal!', text: 'Terjadi kesalahan saat menghapus data.' });
                    }
                });
            }
        });
    });
});
</script>
@endpush
