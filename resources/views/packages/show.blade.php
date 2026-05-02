@extends('layouts.admin')

@section('title', 'Detail Paket Foto')
@section('page-title', 'Detail Paket Foto')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('packages.index') }}">Paket Foto</a></li>
    <li class="breadcrumb-item active">{{ $package->name }}</li>
@endsection

@section('content')
@php
    $firstTpl = $package->templates->first();
    $derivedPhotoCount = $firstTpl?->photo_slots ?? $package->photo_count ?? 0;
    $derivedSize = $firstTpl?->print_size ?? $package->print_size ?? '';
    $sizeLabels = [
        'strip' => 'Photo Strip',
        '4R' => '4R',
        'A4' => 'A4',
        'A3' => 'A3',
    ];
    $sizeIcons = [
        'strip' => 'far fa-id-card',
        '4R' => 'far fa-image',
        'A4' => 'far fa-file-alt',
        'A3' => 'far fa-file',
    ];
    $statusColors = [
        'paid' => ['bg' => 'rgba(34,197,94,0.12)', 'fg' => '#15803d'],
        'pending' => ['bg' => 'rgba(232,201,0,0.12)', 'fg' => '#b45309'],
        'expired' => ['bg' => 'rgba(239,68,68,0.12)', 'fg' => '#b91c1c'],
        'failed' => ['bg' => 'rgba(0,0,0,0.06)', 'fg' => '#27272a'],
        'cancelled' => ['bg' => 'rgba(0,0,0,0.06)', 'fg' => '#71717a'],
    ];
@endphp

<div class="row mb-4">
    <div class="col-12">
        {{-- ─────────── Header card ─────────── --}}
        <div class="pkg-detail-head">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                <div class="d-flex align-items-start gap-3">
                    <div class="pkg-detail-icon">
                        <i class="fas fa-tag"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            @if($package->is_active)
                                <span class="pkg-status-pill pkg-status-active">
                                    <span class="pkg-status-dot"></span> Aktif
                                </span>
                            @else
                                <span class="pkg-status-pill pkg-status-inactive">Nonaktif</span>
                            @endif
                            <span class="pkg-id-pill">#{{ $package->id }}</span>
                        </div>
                        <h3 class="pkg-detail-name mb-1">{{ $package->name }}</h3>
                        <p class="pkg-detail-desc mb-0">
                            {{ $package->description ?: 'Tidak ada deskripsi.' }}
                        </p>
                    </div>
                </div>

                <div class="d-flex gap-2 flex-wrap pkg-detail-actions">
                    <a href="{{ route('packages.edit', $package) }}" class="btn btn-dark pkg-btn-icon">
                        <i class="fas fa-pencil-alt me-1"></i> Edit Paket
                    </a>
                    <button type="button" class="btn btn-outline-danger pkg-btn-icon btn-delete"
                            data-name="{{ $package->name }}"
                            data-url="{{ route('packages.destroy', $package) }}">
                        <i class="far fa-trash-alt me-1"></i> Hapus
                    </button>
                    <a href="{{ route('packages.index') }}" class="btn btn-outline-secondary pkg-btn-icon">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- ─────────── LEFT: specs + price + templates ─────────── --}}
    <div class="col-xl-8">
        {{-- Big price card --}}
        <div class="pkg-price-card mb-4">
            <div>
                <span class="pkg-price-label">Harga Paket</span>
                <div class="pkg-price-amount">
                    <span class="pkg-price-curr">Rp</span>
                    <span>{{ number_format($package->price, 0, ',', '.') }}</span>
                </div>
                <span class="pkg-price-sub">per sesi</span>
            </div>
            <div class="pkg-price-sticker">
                <span>{{ $package->print_copies }}</span>
                <small>lembar cetak</small>
            </div>
        </div>

        {{-- Specs cards --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="pkg-spec-card">
                    <div class="pkg-spec-card-icon"><i class="fas fa-camera"></i></div>
                    <div>
                        <span class="pkg-spec-card-label">Foto Diambil</span>
                        <strong class="pkg-spec-card-value">{{ $derivedPhotoCount > 0 ? $derivedPhotoCount.' foto' : '—' }}</strong>
                        <small class="pkg-spec-card-hint">jepretan kamera per sesi</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="pkg-spec-card">
                    <div class="pkg-spec-card-icon"><i class="{{ $sizeIcons[$derivedSize] ?? 'far fa-image' }}"></i></div>
                    <div>
                        <span class="pkg-spec-card-label">Ukuran Cetak</span>
                        <strong class="pkg-spec-card-value">{{ $derivedSize ? ($sizeLabels[$derivedSize] ?? strtoupper($derivedSize)) : '—' }}</strong>
                        <small class="pkg-spec-card-hint">otomatis dari template</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="pkg-spec-card">
                    <div class="pkg-spec-card-icon"><i class="fas fa-print"></i></div>
                    <div>
                        <span class="pkg-spec-card-label">Lembar Cetak</span>
                        <strong class="pkg-spec-card-value">{{ $package->print_copies }} lembar</strong>
                        <small class="pkg-spec-card-hint">salinan keluar dari printer</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Templates --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between pb-0">
                <div>
                    <h5 class="card-title mb-1">Template Tersedia</h5>
                    <p class="text-muted mb-0 small">Pelanggan bisa pilih salah satu template ini saat sesi foto.</p>
                </div>
                <span class="badge bg-soft-primary text-primary">{{ $package->templates->count() }} template</span>
            </div>
            <div class="card-body">
                @if($package->templates->isNotEmpty())
                    <div class="row g-3">
                        @foreach($package->templates as $template)
                            @php
                                $tplPreviewPath = $template->thumbnail_path ?: $template->frame_path;
                                $tplPreviewUrl = $tplPreviewPath ? \Illuminate\Support\Facades\Storage::url($tplPreviewPath) : null;
                            @endphp
                            <div class="col-md-6 col-lg-4">
                                <div class="pkg-tpl-tile">
                                    <div class="pkg-tpl-thumb">
                                        @if($tplPreviewUrl)
                                            <img src="{{ $tplPreviewUrl }}" alt="{{ $template->name }}">
                                        @else
                                            <div class="pkg-tpl-thumb-empty"><i class="far fa-image"></i></div>
                                        @endif
                                    </div>
                                    <div class="pkg-tpl-info">
                                        <h6 class="mb-1">{{ $template->name }}</h6>
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <span class="pkg-tpl-badge">{{ $sizeLabels[$template->print_size] ?? strtoupper($template->print_size) }}</span>
                                            <span class="text-muted small">{{ $template->photo_slots }} slot</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="far fa-image" style="font-size:2rem;opacity:0.4;"></i>
                        <p class="mb-0 mt-2 small">Belum ada template untuk paket ini.</p>
                        <a href="{{ route('packages.edit', $package) }}" class="btn btn-sm btn-outline-dark mt-2">
                            <i class="fas fa-plus me-1"></i> Tambah Template
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Recent transactions --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between pb-0">
                <div>
                    <h5 class="card-title mb-1">Transaksi Terbaru</h5>
                    <p class="text-muted mb-0 small">8 transaksi terakhir untuk paket ini.</p>
                </div>
                <a href="{{ route('transactions.index', ['package_id' => $package->id]) }}" class="btn btn-sm btn-soft-primary">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                @if($recentTransactions->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Order ID</th>
                                    <th>Cabang</th>
                                    <th>Status</th>
                                    <th class="text-end">Nominal</th>
                                    <th class="text-end pe-4">Waktu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentTransactions as $tx)
                                    @php $sc = $statusColors[$tx->status] ?? ['bg' => 'rgba(0,0,0,0.05)', 'fg' => '#71717a']; @endphp
                                    <tr>
                                        <td class="ps-4 fw-semibold">{{ $tx->order_id }}</td>
                                        <td>{{ $tx->branch?->name ?? '-' }}</td>
                                        <td>
                                            <span class="pkg-tx-status" style="background:{{ $sc['bg'] }};color:{{ $sc['fg'] }};">
                                                {{ strtoupper($tx->status) }}
                                            </span>
                                        </td>
                                        <td class="text-end fw-semibold">Rp {{ number_format($tx->amount, 0, ',', '.') }}</td>
                                        <td class="text-end pe-4 text-muted small">{{ $tx->created_at->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-receipt" style="font-size:2rem;opacity:0.4;"></i>
                        <p class="mb-0 mt-2 small">Belum ada transaksi untuk paket ini.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ─────────── RIGHT: stats sidebar ─────────── --}}
    <div class="col-xl-4">
        <div class="pkg-stats-card mb-4">
            <h6 class="pkg-stats-title">Performa Paket</h6>

            <div class="pkg-stat-row">
                <div class="pkg-stat-icon" style="background:#f0fdf4;color:#15803d;">
                    <i class="fas fa-cash-register"></i>
                </div>
                <div class="flex-grow-1">
                    <span class="pkg-stat-label">Total Revenue</span>
                    <strong class="pkg-stat-value">Rp {{ number_format($stats['revenue'], 0, ',', '.') }}</strong>
                </div>
            </div>

            <div class="pkg-stat-row">
                <div class="pkg-stat-icon" style="background:#eff6ff;color:#1d4ed8;">
                    <i class="far fa-credit-card"></i>
                </div>
                <div class="flex-grow-1">
                    <span class="pkg-stat-label">Transaksi Terbayar</span>
                    <strong class="pkg-stat-value">{{ number_format($stats['paid_count']) }} / {{ number_format($stats['total_count']) }}</strong>
                </div>
            </div>

            <div class="pkg-stat-row">
                <div class="pkg-stat-icon" style="background:#fef3c7;color:#b45309;">
                    <i class="far fa-clock"></i>
                </div>
                <div class="flex-grow-1">
                    <span class="pkg-stat-label">Pending Payment</span>
                    <strong class="pkg-stat-value">{{ number_format($stats['pending_count']) }}</strong>
                </div>
            </div>

            <div class="pkg-stat-row">
                <div class="pkg-stat-icon" style="background:#fef9c3;color:#a16207;">
                    <i class="far fa-calendar-check"></i>
                </div>
                <div class="flex-grow-1">
                    <span class="pkg-stat-label">Terakhir Terjual</span>
                    <strong class="pkg-stat-value">
                        {{ $stats['last_sold_at'] ? \Carbon\Carbon::parse($stats['last_sold_at'])->diffForHumans() : 'Belum pernah' }}
                    </strong>
                </div>
            </div>
        </div>

        <div class="pkg-meta-card">
            <h6 class="pkg-stats-title">Informasi Sistem</h6>
            <div class="pkg-meta-row">
                <span class="pkg-meta-label">ID Paket</span>
                <strong class="pkg-meta-value">#{{ $package->id }}</strong>
            </div>
            <div class="pkg-meta-row">
                <span class="pkg-meta-label">Dibuat</span>
                <strong class="pkg-meta-value">{{ $package->created_at->format('d M Y, H:i') }}</strong>
            </div>
            <div class="pkg-meta-row">
                <span class="pkg-meta-label">Terakhir Diubah</span>
                <strong class="pkg-meta-value">{{ $package->updated_at->diffForHumans() }}</strong>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* ── Header card ── */
    .pkg-detail-head {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.05);
        border-radius: 18px;
        padding: 1.5rem 1.75rem;
        box-shadow: 0 2px 6px rgba(0,0,0,0.03);
    }
    .pkg-detail-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        background: linear-gradient(135deg, #fef9c3 0%, #fde68a 100%);
        color: #b45309;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }
    .pkg-detail-name {
        font-size: 1.5rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        color: #18181b;
    }
    .pkg-detail-desc {
        color: #71717a;
        font-size: 0.92rem;
        line-height: 1.55;
        max-width: 600px;
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
    .pkg-status-active { background: rgba(34,197,94,0.12); color: #15803d; }
    .pkg-status-inactive { background: rgba(0,0,0,0.05); color: #71717a; }
    .pkg-status-dot {
        width: 6px;
        height: 6px;
        background: #22c55e;
        border-radius: 50%;
        box-shadow: 0 0 0 2px rgba(34,197,94,0.18);
    }
    .pkg-id-pill {
        background: #fafaf5;
        color: #71717a;
        font-size: 0.7rem;
        font-weight: 600;
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        font-family: ui-monospace, SFMono-Regular, monospace;
    }
    .pkg-btn-icon {
        font-weight: 600;
        border-radius: 10px;
        padding: 0.55rem 1rem;
        font-size: 0.86rem;
    }
    .btn-dark.pkg-btn-icon {
        background: #18181b !important;
        border-color: #18181b !important;
    }

    /* ── Big price card ── */
    .pkg-price-card {
        background: linear-gradient(135deg, #18181b 0%, #27272a 100%);
        border-radius: 18px;
        padding: 1.75rem 2rem;
        color: #fafaf5;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 14px 30px -14px rgba(24,24,27,0.4);
    }
    .pkg-price-card::before {
        content: '';
        position: absolute;
        top: -80px;
        right: -60px;
        width: 240px;
        height: 240px;
        background: radial-gradient(circle, rgba(232,201,0,0.18) 0%, transparent 65%);
        filter: blur(30px);
        pointer-events: none;
    }
    .pkg-price-label {
        display: block;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: #E8C900;
        font-weight: 600;
        margin-bottom: 0.4rem;
    }
    .pkg-price-amount {
        display: flex;
        align-items: baseline;
        gap: 0.4rem;
        font-variant-numeric: tabular-nums;
    }
    .pkg-price-curr {
        font-size: 1.05rem;
        font-weight: 600;
        color: rgba(250,250,245,0.6);
    }
    .pkg-price-amount > span:last-child {
        font-size: 2.4rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: #fafaf5;
        line-height: 1;
    }
    .pkg-price-sub {
        font-size: 0.78rem;
        color: rgba(250,250,245,0.55);
        margin-top: 0.35rem;
        display: inline-block;
    }
    .pkg-price-sticker {
        background: rgba(232,201,0,0.15);
        border: 1.5px solid rgba(232,201,0,0.4);
        border-radius: 14px;
        padding: 1rem 1.25rem;
        text-align: center;
        position: relative;
        z-index: 1;
        min-width: 96px;
    }
    .pkg-price-sticker span {
        display: block;
        font-size: 1.85rem;
        font-weight: 800;
        color: #E8C900;
        letter-spacing: -0.02em;
        line-height: 1;
        font-variant-numeric: tabular-nums;
    }
    .pkg-price-sticker small {
        display: block;
        font-size: 0.68rem;
        color: rgba(250,250,245,0.65);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-top: 0.4rem;
        font-weight: 600;
    }

    /* ── Spec cards ── */
    .pkg-spec-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.05);
        border-radius: 14px;
        padding: 1.1rem 1.2rem;
        display: flex;
        align-items: center;
        gap: 0.85rem;
        height: 100%;
        transition: border-color 0.18s, transform 0.18s;
        box-shadow: 0 2px 6px rgba(0,0,0,0.03);
    }
    .pkg-spec-card:hover {
        border-color: rgba(0,0,0,0.12);
        transform: translateY(-1px);
    }
    .pkg-spec-card-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: #fef9c3;
        color: #b45309;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }
    .pkg-spec-card-label {
        display: block;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #71717a;
        font-weight: 600;
        line-height: 1;
    }
    .pkg-spec-card-value {
        display: block;
        font-size: 1rem;
        font-weight: 700;
        color: #18181b;
        margin-top: 4px;
        letter-spacing: -0.01em;
    }
    .pkg-spec-card-hint {
        display: block;
        font-size: 0.7rem;
        color: #a1a1aa;
        margin-top: 2px;
    }

    /* ── Template tiles ── */
    .pkg-tpl-tile {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.06);
        border-radius: 12px;
        padding: 0.65rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        transition: border-color 0.18s, box-shadow 0.18s;
    }
    .pkg-tpl-tile:hover {
        border-color: rgba(0,0,0,0.14);
        box-shadow: 0 6px 14px -6px rgba(0,0,0,0.1);
    }
    .pkg-tpl-thumb {
        width: 56px;
        height: 56px;
        border-radius: 8px;
        overflow: hidden;
        flex-shrink: 0;
        background: #fafaf5;
        border: 1px solid rgba(0,0,0,0.05);
    }
    .pkg-tpl-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .pkg-tpl-thumb-empty {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #a1a1aa;
        font-size: 1.2rem;
    }
    .pkg-tpl-info {
        flex: 1;
        min-width: 0;
    }
    .pkg-tpl-info h6 {
        font-size: 0.88rem;
        font-weight: 600;
        color: #18181b;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .pkg-tpl-badge {
        display: inline-block;
        padding: 1px 7px;
        background: #fef9c3;
        color: #b45309;
        border-radius: 4px;
        font-weight: 600;
        font-size: 0.68rem;
    }

    /* ── Stats sidebar ── */
    .pkg-stats-card,
    .pkg-meta-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.05);
        border-radius: 16px;
        padding: 1.5rem 1.4rem;
        box-shadow: 0 2px 6px rgba(0,0,0,0.03);
    }
    .pkg-stats-title {
        font-weight: 700;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #71717a;
        margin-bottom: 1.1rem;
    }
    .pkg-stat-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.7rem 0;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    .pkg-stat-row:last-child { border-bottom: none; }
    .pkg-stat-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .pkg-stat-label {
        display: block;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #a1a1aa;
        font-weight: 600;
        line-height: 1;
    }
    .pkg-stat-value {
        display: block;
        font-size: 0.95rem;
        font-weight: 700;
        color: #18181b;
        margin-top: 4px;
        font-variant-numeric: tabular-nums;
        letter-spacing: -0.01em;
    }

    .pkg-meta-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.55rem 0;
        border-bottom: 1px dashed rgba(0,0,0,0.06);
    }
    .pkg-meta-row:last-child { border-bottom: none; }
    .pkg-meta-label {
        font-size: 0.78rem;
        color: #71717a;
    }
    .pkg-meta-value {
        font-size: 0.82rem;
        color: #18181b;
        font-weight: 600;
    }

    /* ── Tx status pill ── */
    .pkg-tx-status {
        display: inline-block;
        font-size: 0.68rem;
        font-weight: 700;
        padding: 0.25rem 0.6rem;
        border-radius: 999px;
        letter-spacing: 0.04em;
    }
</style>
@endpush

@push('scripts')
<script>
$(function () {
    $('.btn-delete').on('click', function () {
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
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false,
                        }).then(function () {
                            window.location.href = '{{ route('packages.index') }}';
                        });
                    },
                    error: function () {
                        Swal.fire({ icon: 'error', title: 'Gagal!', text: 'Terjadi kesalahan saat menghapus data.' });
                    },
                });
            }
        });
    });
});
</script>
@endpush
