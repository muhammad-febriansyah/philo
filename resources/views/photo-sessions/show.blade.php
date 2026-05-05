@extends('layouts.admin')

@section('title', 'Detail Sesi Foto')
@section('page-title', 'Detail Sesi Foto')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('photo-sessions.index') }}">Sesi Foto</a></li>
    <li class="breadcrumb-item active">{{ $session->transaction->order_id ?? '#'.$session->id }}</li>
@endsection

@push('styles')
<style>
    :root {
        --psd-ink: #18181b;
        --psd-muted: #71717a;
        --psd-line: #e4e4e7;
        --psd-bg: #fafaf5;
        --psd-yellow: #E8C900;
        --psd-yellow-deep: #b89e00;
    }

    .psd-card {
        background: #fff;
        border: 1px solid var(--psd-line);
        border-radius: 16px;
        overflow: hidden;
    }

    .psd-card-pad { padding: 1.25rem 1.5rem; }

    .psd-eyebrow {
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: var(--psd-muted);
        margin-bottom: 0.4rem;
    }

    .psd-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.3rem 0.75rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }
    .psd-status-pill .dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
    }
    .psd-status-completed { background: rgba(34,197,94,0.12); color: #15803d; }
    .psd-status-capturing { background: rgba(14,165,233,0.12); color: #0369a1; }
    .psd-status-pending { background: rgba(232,201,0,0.18); color: #92750a; }
    .psd-status-failed { background: rgba(239,68,68,0.12); color: #b91c1c; }
    .psd-status-default { background: rgba(0,0,0,0.06); color: #3f3f46; }

    .psd-id-pill {
        display: inline-flex;
        align-items: center;
        font-size: 0.72rem;
        font-weight: 700;
        background: rgba(24,24,27,0.06);
        color: var(--psd-ink);
        padding: 0.3rem 0.7rem;
        border-radius: 999px;
        font-family: 'SFMono-Regular', Menlo, monospace;
    }

    .psd-stat {
        background: linear-gradient(135deg, #fff 0%, #fafaf5 100%);
        border: 1px solid var(--psd-line);
        border-radius: 14px;
        padding: 1rem 1.1rem;
        height: 100%;
    }
    .psd-stat-label {
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--psd-muted);
    }
    .psd-stat-value {
        font-size: 1.55rem;
        font-weight: 800;
        color: var(--psd-ink);
        letter-spacing: -0.025em;
        margin-top: 0.25rem;
    }
    .psd-stat-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: rgba(232,201,0,0.16);
        color: var(--psd-yellow-deep);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    .psd-final {
        background: #0a0a0a;
        border-radius: 16px;
        position: relative;
        overflow: hidden;
        min-height: 420px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .psd-final img {
        max-width: 100%;
        max-height: 70vh;
        object-fit: contain;
        display: block;
    }
    .psd-final-empty {
        color: rgba(255,255,255,0.5);
        text-align: center;
        padding: 3rem 1.5rem;
    }
    .psd-final-empty i {
        font-size: 3rem;
        margin-bottom: 0.75rem;
    }

    .psd-side-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.85rem 0;
        border-bottom: 1px dashed var(--psd-line);
    }
    .psd-side-row:last-child { border-bottom: none; }
    .psd-side-label {
        font-size: 0.78rem;
        color: var(--psd-muted);
        font-weight: 500;
    }
    .psd-side-value {
        font-size: 0.88rem;
        font-weight: 600;
        color: var(--psd-ink);
        text-align: right;
        word-break: break-word;
    }

    .psd-timeline {
        position: relative;
        padding-left: 1.5rem;
    }
    .psd-timeline::before {
        content: '';
        position: absolute;
        left: 12px;
        top: 8px;
        bottom: 8px;
        width: 2px;
        background: var(--psd-line);
    }
    .psd-timeline-item {
        position: relative;
        padding-bottom: 1.1rem;
    }
    .psd-timeline-item:last-child { padding-bottom: 0; }
    .psd-timeline-dot {
        position: absolute;
        left: -1.5rem;
        top: 2px;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: #fff;
        border: 2px solid var(--psd-line);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.78rem;
    }
    .psd-timeline-label {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--psd-ink);
        line-height: 1.2;
    }
    .psd-timeline-time {
        font-size: 0.72rem;
        color: var(--psd-muted);
        margin-top: 0.15rem;
    }
    .psd-timeline-desc {
        font-size: 0.75rem;
        color: var(--psd-muted);
        margin-top: 0.15rem;
        font-style: italic;
    }

    .psd-photo-cell {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
        background: #f4f4ed;
        border: 1px solid var(--psd-line);
        transition: transform .2s, box-shadow .2s;
    }
    .psd-photo-cell:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 24px -10px rgba(0,0,0,0.18);
    }
    .psd-photo-cell img {
        width: 100%;
        aspect-ratio: 1 / 1;
        object-fit: cover;
        display: block;
    }
    .psd-photo-cell .order-tag {
        position: absolute;
        top: 8px;
        left: 8px;
        background: rgba(0,0,0,0.65);
        color: #fff;
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.2rem 0.5rem;
        border-radius: 999px;
    }
    .psd-photo-actions {
        display: flex;
        gap: 0.4rem;
        padding: 0.55rem;
        background: #fff;
    }

    .psd-empty {
        text-align: center;
        padding: 3rem 1rem;
        color: var(--psd-muted);
    }
    .psd-empty i { font-size: 3rem; margin-bottom: 0.5rem; }

    .psd-headline {
        font-size: 1.55rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: var(--psd-ink);
    }
    .psd-orderid {
        font-family: 'SFMono-Regular', Menlo, monospace;
        font-weight: 700;
        color: var(--psd-yellow-deep);
    }

    .psd-action-btn {
        border-radius: 12px;
        padding: 0.6rem 1.1rem;
        font-weight: 600;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border: 1px solid var(--psd-line);
        background: #fff;
        color: var(--psd-ink);
        transition: all 0.18s;
    }
    .psd-action-btn:hover { background: #fafaf5; border-color: #d4d4d8; color: var(--psd-ink); }
    .psd-action-primary {
        background: var(--psd-yellow);
        border-color: var(--psd-yellow);
        color: var(--psd-ink);
        box-shadow: 0 4px 14px rgba(232,201,0,0.3);
    }
    .psd-action-primary:hover { background: var(--psd-yellow-deep); border-color: var(--psd-yellow-deep); color: #fff; }
    .psd-action-danger {
        color: #b91c1c;
        border-color: rgba(239,68,68,0.3);
    }
    .psd-action-danger:hover { background: #fef2f2; color: #b91c1c; border-color: #fca5a5; }
</style>
@endpush

@php
    $statusClass = [
        'completed' => 'psd-status-completed',
        'capturing' => 'psd-status-capturing',
        'pending' => 'psd-status-pending',
        'failed' => 'psd-status-failed',
    ][$session->status] ?? 'psd-status-default';

    $tx = $session->transaction;
    $branch = $session->branch;
    $template = $session->template;
    $voucher = $tx?->voucher;

    $bytesFormat = function (int $bytes) {
        if ($bytes <= 0) return '-';
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        $value = (float) $bytes;
        while ($value >= 1024 && $i < count($units) - 1) {
            $value /= 1024;
            $i++;
        }
        return number_format($value, $value >= 100 ? 0 : 1).' '.$units[$i];
    };

    $durationFormat = function (?int $seconds) {
        if ($seconds === null) return '-';
        if ($seconds < 60) return $seconds.' detik';
        $minutes = intdiv($seconds, 60);
        $remaining = $seconds % 60;
        return $minutes.' mnt '.$remaining.' dtk';
    };
@endphp

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="mdi mdi-check-circle-outline me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="mdi mdi-alert-circle-outline me-1"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ─────────── Header ─────────── --}}
<div class="psd-card mb-4">
    <div class="psd-card-pad">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <span class="psd-status-pill {{ $statusClass }}">
                    <span class="dot"></span> {{ strtoupper($session->status) }}
                </span>
                <span class="psd-id-pill">#{{ $session->id }}</span>
                @if($tx)
                    <h2 class="psd-headline mb-0">
                        <span class="psd-orderid">{{ $tx->order_id }}</span>
                    </h2>
                @endif
                @if($session->completed_at)
                    <span class="text-muted small">
                        <i class="mdi mdi-clock-check-outline me-1"></i>
                        Selesai {{ $session->completed_at->format('d M Y, H:i') }}
                    </span>
                @endif
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if($branch)
                    <a href="{{ url('booth/'.$branch->code) }}" target="_blank" class="psd-action-btn">
                        <i class="mdi mdi-monitor"></i> Buka Booth
                    </a>
                @endif
                @if($finalImageUrl)
                    <a href="{{ route('photo-sessions.download-all', $session) }}" class="psd-action-btn">
                        <i class="mdi mdi-folder-zip-outline"></i> Download ZIP
                    </a>
                    <button type="button" class="psd-action-btn psd-action-primary" id="btn-print">
                        <i class="mdi mdi-printer"></i> Cetak Ulang
                    </button>
                @endif
                <form method="POST" action="{{ route('photo-sessions.destroy', $session) }}"
                      onsubmit="return confirm('Hapus sesi {{ $tx?->order_id ?? $session->id }}? Foto akan ikut dihapus permanen.')"
                      class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="psd-action-btn psd-action-danger">
                        <i class="mdi mdi-trash-can-outline"></i> Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ─────────── Stats grid ─────────── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="psd-stat">
            <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="psd-stat-label">Foto</span>
                <span class="psd-stat-icon"><i class="mdi mdi-image-multiple"></i></span>
            </div>
            <div class="psd-stat-value">{{ $session->photos->count() }}</div>
            <div class="text-muted small">{{ $bytesFormat($totalOriginalBytes) }} total</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="psd-stat">
            <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="psd-stat-label">Durasi Sesi</span>
                <span class="psd-stat-icon"><i class="mdi mdi-timer"></i></span>
            </div>
            <div class="psd-stat-value">{{ $durationFormat($duration) }}</div>
            <div class="text-muted small">
                {{ $session->created_at?->format('H:i') ?? '-' }} → {{ $session->completed_at?->format('H:i') ?? '...' }}
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="psd-stat">
            <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="psd-stat-label">Cetak</span>
                <span class="psd-stat-icon"><i class="mdi mdi-printer"></i></span>
            </div>
            <div class="psd-stat-value">{{ 1 + ($tx?->extra_prints ?? 0) }}<span class="text-muted small fw-normal">×</span></div>
            <div class="text-muted small">
                @if(($tx?->extra_prints ?? 0) > 0)
                    1 default + {{ $tx->extra_prints }} extra
                @else
                    Default 1 cetak
                @endif
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="psd-stat">
            <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="psd-stat-label">Total Bayar</span>
                <span class="psd-stat-icon"><i class="mdi mdi-currency-usd"></i></span>
            </div>
            <div class="psd-stat-value">
                @if($tx) Rp {{ number_format((int) $tx->amount, 0, ',', '.') }} @else - @endif
            </div>
            <div class="text-muted small">
                @if($tx?->discount_amount > 0)
                    Disc Rp {{ number_format((float) $tx->discount_amount, 0, ',', '.') }}
                @else
                    {{ strtoupper((string) ($tx?->payment_method ?? '-')) }}
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ─────────── Main grid: final image + sidebar ─────────── --}}
<div class="row g-4 mb-4">
    {{-- Final image preview --}}
    <div class="col-lg-8">
        <div class="psd-card">
            <div class="psd-card-pad d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <p class="psd-eyebrow mb-0">Hasil Akhir</p>
                    <h6 class="mb-0">{{ $template?->name ?? 'Tanpa template' }}</h6>
                </div>
                @if($finalImageUrl)
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ $finalImageUrl }}" target="_blank" class="psd-action-btn">
                            <i class="mdi mdi-eye-outline"></i> Buka Tab Baru
                        </a>
                        <a href="{{ $finalImageUrl }}" download class="psd-action-btn psd-action-primary">
                            <i class="mdi mdi-download"></i> Download · {{ $bytesFormat($finalImageBytes) }}
                        </a>
                    </div>
                @endif
            </div>
            <div class="psd-final">
                @if($finalImageUrl)
                    <img id="final-image" src="{{ $finalImageUrl }}" alt="Hasil cetak {{ $tx?->order_id }}">
                @else
                    <div class="psd-final-empty">
                        <i class="mdi mdi-image-off-outline d-block"></i>
                        <p class="mb-0 fw-semibold">Belum ada hasil akhir</p>
                        <small>Sesi mungkin belum selesai atau gagal compose.</small>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Sidebar: details + email + timeline --}}
    <div class="col-lg-4">
        {{-- Customer email --}}
        <div class="psd-card mb-3">
            <div class="psd-card-pad">
                <p class="psd-eyebrow mb-1">Kontak Pelanggan</p>
                @if($session->customer_email)
                    <p class="fw-bold mb-1 text-break">{{ $session->customer_email }}</p>
                    <small class="text-muted d-block mb-3">Email tersimpan dari sesi.</small>
                @else
                    <p class="text-muted small mb-3"><i class="mdi mdi-information-outline me-1"></i>Belum ada email tercatat.</p>
                @endif

                @if($finalImageUrl)
                    <form method="POST" action="{{ route('photo-sessions.resend-email', $session) }}" class="d-flex flex-column gap-2">
                        @csrf
                        <input type="email" name="email" required
                               value="{{ old('email', $session->customer_email) }}"
                               placeholder="nama@email.com"
                               class="form-control form-control-sm @error('email') is-invalid @enderror"
                               {{ $mailEnabled ? '' : 'disabled' }}>
                        @error('email')<small class="text-danger">{{ $message }}</small>@enderror
                        <button type="submit" class="psd-action-btn psd-action-primary justify-content-center"
                                {{ $mailEnabled ? '' : 'disabled' }}>
                            <i class="mdi mdi-email-send-outline"></i> Kirim Hasil Foto
                        </button>
                        @if(!$mailEnabled)
                            <small class="text-muted text-center"><i class="mdi mdi-alert-circle-outline me-1"></i>Mailketing belum dikonfigurasi.</small>
                        @endif
                    </form>
                @endif
            </div>
        </div>

        {{-- Detail panel --}}
        <div class="psd-card mb-3">
            <div class="psd-card-pad">
                <p class="psd-eyebrow mb-2">Informasi Sesi</p>
                <div class="psd-side-row">
                    <span class="psd-side-label">Cabang</span>
                    <span class="psd-side-value">{{ $branch?->name ?? '-' }}</span>
                </div>
                <div class="psd-side-row">
                    <span class="psd-side-label">Kode Cabang</span>
                    <span class="psd-side-value"><code>{{ $branch?->code ?? '-' }}</code></span>
                </div>
                <div class="psd-side-row">
                    <span class="psd-side-label">Template</span>
                    <span class="psd-side-value">{{ $template?->name ?? '-' }}</span>
                </div>
                <div class="psd-side-row">
                    <span class="psd-side-label">Ukuran Cetak</span>
                    <span class="psd-side-value">{{ strtoupper((string) ($template?->print_size ?? '-')) }}</span>
                </div>
                <div class="psd-side-row">
                    <span class="psd-side-label">Slot Foto</span>
                    <span class="psd-side-value">{{ $template?->photo_slots ?? '-' }}</span>
                </div>
                <div class="psd-side-row">
                    <span class="psd-side-label">Status</span>
                    <span class="psd-side-value">
                        <span class="psd-status-pill {{ $statusClass }}">{{ strtoupper($session->status) }}</span>
                    </span>
                </div>
                <div class="psd-side-row">
                    <span class="psd-side-label">Dibuat</span>
                    <span class="psd-side-value">{{ $session->created_at?->format('d M Y, H:i') ?? '-' }}</span>
                </div>
                <div class="psd-side-row">
                    <span class="psd-side-label">Diperbarui</span>
                    <span class="psd-side-value">{{ $session->updated_at?->format('d M Y, H:i') ?? '-' }}</span>
                </div>
            </div>
        </div>

        {{-- Transaction --}}
        @if($tx)
        <div class="psd-card mb-3">
            <div class="psd-card-pad">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <p class="psd-eyebrow mb-0">Transaksi</p>
                    <a href="{{ route('transactions.show', $tx) }}" class="text-decoration-none small text-muted">
                        Lihat <i class="mdi mdi-arrow-right"></i>
                    </a>
                </div>
                <div class="psd-side-row">
                    <span class="psd-side-label">Order ID</span>
                    <span class="psd-side-value text-break"><code>{{ $tx->order_id }}</code></span>
                </div>
                <div class="psd-side-row">
                    <span class="psd-side-label">Pembayaran</span>
                    <span class="psd-side-value">{{ strtoupper((string) ($tx->payment_method ?? '-')) }}</span>
                </div>
                @if($tx->original_amount && $tx->discount_amount > 0)
                <div class="psd-side-row">
                    <span class="psd-side-label">Original</span>
                    <span class="psd-side-value text-decoration-line-through text-muted">
                        Rp {{ number_format((float) $tx->original_amount, 0, ',', '.') }}
                    </span>
                </div>
                <div class="psd-side-row">
                    <span class="psd-side-label">Diskon</span>
                    <span class="psd-side-value text-success">
                        − Rp {{ number_format((float) $tx->discount_amount, 0, ',', '.') }}
                    </span>
                </div>
                @endif
                <div class="psd-side-row">
                    <span class="psd-side-label">Total Dibayar</span>
                    <span class="psd-side-value fw-bold">Rp {{ number_format((int) $tx->amount, 0, ',', '.') }}</span>
                </div>
                @if($voucher)
                <div class="psd-side-row">
                    <span class="psd-side-label">Voucher</span>
                    <span class="psd-side-value">
                        <code>{{ $voucher->code }}</code>
                    </span>
                </div>
                @endif
                <div class="psd-side-row">
                    <span class="psd-side-label">Dibayar</span>
                    <span class="psd-side-value">{{ $tx->paid_at?->format('d M Y, H:i') ?? '-' }}</span>
                </div>
                @if($tx->duitku_reference)
                <div class="psd-side-row">
                    <span class="psd-side-label">Ref Gateway</span>
                    <span class="psd-side-value text-break"><code class="font-size-11">{{ $tx->duitku_reference }}</code></span>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Timeline --}}
        @if(!empty($timeline))
        <div class="psd-card">
            <div class="psd-card-pad">
                <p class="psd-eyebrow mb-3">Linimasa</p>
                <div class="psd-timeline">
                    @foreach($timeline as $entry)
                        <div class="psd-timeline-item">
                            <span class="psd-timeline-dot" style="border-color: {{ $entry['color'] }}; color: {{ $entry['color'] }};">
                                <i class="mdi {{ $entry['icon'] }}"></i>
                            </span>
                            <div>
                                <div class="psd-timeline-label">{{ $entry['label'] }}</div>
                                <div class="psd-timeline-time">{{ $entry['time']->format('d M Y, H:i:s') }}</div>
                                @if(!empty($entry['description']))
                                    <div class="psd-timeline-desc">{{ $entry['description'] }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ─────────── Original captures ─────────── --}}
<div class="psd-card">
    <div class="psd-card-pad d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <p class="psd-eyebrow mb-0">Foto Asli</p>
            <h6 class="mb-0">Captures Original ({{ $session->photos->count() }})</h6>
        </div>
        @if($session->photos->isNotEmpty())
            <a href="{{ route('photo-sessions.download-all', $session) }}" class="psd-action-btn">
                <i class="mdi mdi-folder-zip-outline"></i> Download Semua (ZIP)
            </a>
        @endif
    </div>
    <div class="psd-card-pad pt-0">
        @if($session->photos->isEmpty())
            <div class="psd-empty">
                <i class="mdi mdi-image-off-outline d-block"></i>
                <p class="fw-semibold mb-0">Tidak ada foto asli</p>
                <small>Sesi belum mencapai tahap capture atau gagal upload.</small>
            </div>
        @else
            <div class="row g-3">
                @foreach($session->photos as $photo)
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                        <div class="psd-photo-cell">
                            <span class="order-tag">#{{ $photo->order }}</span>
                            <a href="{{ asset('storage/'.$photo->original_path) }}" target="_blank">
                                <img src="{{ asset('storage/'.$photo->original_path) }}" alt="Foto {{ $photo->order }}" loading="lazy">
                            </a>
                            <div class="psd-photo-actions">
                                <a href="{{ route('photos.download', $photo) }}"
                                   class="psd-action-btn flex-grow-1 justify-content-center"
                                   style="padding: 0.4rem 0.6rem; font-size: 0.78rem;">
                                    <i class="mdi mdi-download"></i> Download
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('btn-print')?.addEventListener('click', function () {
        var img = document.getElementById('final-image');
        if (!img) return;
        var src = img.getAttribute('src');
        var win = window.open('', '_blank');
        if (!win) return;
        win.document.write(
            '<!doctype html><html><head><title>Cetak {{ $tx?->order_id ?? $session->id }}</title>' +
            '<style>@page{margin:0;}html,body{margin:0;padding:0;background:#fff;}' +
            'div.page{display:flex;align-items:center;justify-content:center;width:100%;height:100vh;}' +
            'img{max-width:100%;max-height:100%;object-fit:contain;-webkit-print-color-adjust:exact;}</style>' +
            '</head><body><div class="page"><img src="' + src + '"></div>' +
            '<script>window.onload=function(){setTimeout(function(){window.print();},400);};<\/script>' +
            '</body></html>'
        );
        win.document.close();
    });
</script>
@endpush
