<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Transaksi — {{ $siteName }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10.5px;
            color: #1a1200;
            background: #ffffff;
        }

        /* ── Header ── */
        .header {
            background: linear-gradient(135deg, #1a1200 0%, #3d2a00 45%, #9e6e00 100%);
            color: #fff;
            padding: 26px 32px;
        }

        .header-inner { display: table; width: 100%; }
        .header-left  { display: table-cell; vertical-align: middle; width: 65%; }
        .header-right { display: table-cell; vertical-align: middle; text-align: right; width: 35%; }

        .header-logo { height: 40px; width: auto; }

        .header-title {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: -0.3px;
            color: #fff;
            margin-bottom: 4px;
        }

        .header-sub { font-size: 11px; color: rgba(255,255,255,0.65); }

        .header-badge {
            display: inline-block;
            background: #C9A800;
            color: #1a1200;
            font-weight: 700;
            font-size: 10px;
            padding: 4px 10px;
            border-radius: 4px;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .header-meta { font-size: 10px; color: rgba(255,255,255,0.6); }

        /* ── Info strip ── */
        .info-strip {
            background: #fff9e0;
            border-bottom: 2px solid #C9A800;
            padding: 10px 32px;
            display: table;
            width: 100%;
        }

        .info-item {
            display: table-cell;
            font-size: 10px;
            color: #4a3700;
            padding-right: 24px;
        }

        .info-item strong { color: #1a1200; }

        /* ── Table ── */
        .table-wrap { padding: 20px 32px; }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        thead tr {
            background: #C9A800;
            color: #1a1200;
        }

        thead th {
            padding: 9px 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 9.5px;
            border: 1px solid #a88e00;
        }

        tbody tr:nth-child(even)  { background: #fffbe8; }
        tbody tr:nth-child(odd)   { background: #ffffff; }

        tbody td {
            padding: 8px 8px;
            border: 1px solid #e5e0c0;
            vertical-align: middle;
        }

        .text-center { text-align: center; }
        .text-right  { text-align: right; }
        .fw-bold     { font-weight: 700; }
        .text-muted  { color: #78716c; }

        /* Status badges */
        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .badge-paid      { background: #d1fae5; color: #065f46; }
        .badge-pending   { background: #fef9c3; color: #854d0e; }
        .badge-expired   { background: #fee2e2; color: #991b1b; }
        .badge-failed    { background: #e5e7eb; color: #111827; }
        .badge-cancelled { background: #f1f5f9; color: #475569; }

        /* ── Footer ── */
        .footer {
            margin: 0 32px;
            padding: 12px 0 0;
            border-top: 1px solid #e5e0c0;
            display: table;
            width: calc(100% - 64px);
        }

        .footer-left  { display: table-cell; font-size: 9.5px; color: #78716c; }
        .footer-right { display: table-cell; text-align: right; font-size: 9.5px; color: #78716c; }

        .summary-box {
            background: #fff9e0;
            border: 1px solid #C9A800;
            border-radius: 6px;
            padding: 10px 14px;
            margin: 0 32px 16px;
            display: table;
            width: calc(100% - 64px);
        }

        .summary-item {
            display: table-cell;
            padding-right: 28px;
            font-size: 10px;
        }

        .summary-value {
            font-size: 14px;
            font-weight: 700;
            color: #1a1200;
        }

        .summary-label { color: #78716c; font-size: 9px; text-transform: uppercase; letter-spacing: 0.05em; }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <div class="header-inner">
            <div class="header-left">
                <div class="header-badge">Laporan Transaksi</div>
                <div class="header-title">Data Transaksi</div>
                <div class="header-sub">{{ $siteName }} &mdash; Dicetak pada {{ now()->format('d F Y, H:i') }} WIB</div>
            </div>
            <div class="header-right">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" class="header-logo" alt="{{ $siteName }}">
                @else
                    <div style="font-size:22px;font-weight:800;color:#C9A800;letter-spacing:-0.5px;">{{ $siteName }}</div>
                @endif
                <div class="header-meta" style="margin-top:6px;">{{ now()->format('d/m/Y') }}</div>
            </div>
        </div>
    </div>

    <!-- Info strip -->
    <div class="info-strip">
        <div class="info-item">
            <strong>Filter Cabang:</strong><br>
            {{ $filterBranch ?? 'Semua Cabang' }}
        </div>
        <div class="info-item">
            <strong>Filter Status:</strong><br>
            {{ $filterStatus ? strtoupper($filterStatus) : 'Semua Status' }}
        </div>
        <div class="info-item">
            <strong>Pencarian:</strong><br>
            {{ $search ?? '—' }}
        </div>
    </div>

    <!-- Summary -->
    <div style="padding: 16px 32px 0;">
        <div class="summary-box">
            <div class="summary-item">
                <div class="summary-value">{{ $transactions->count() }}</div>
                <div class="summary-label">Total Record</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ $transactions->where('status', 'paid')->count() }}</div>
                <div class="summary-label">Transaksi Paid</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">Rp {{ number_format($transactions->where('status', 'paid')->sum('amount'), 0, ',', '.') }}</div>
                <div class="summary-label">Total Revenue</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ $transactions->where('status', 'pending')->count() }}</div>
                <div class="summary-label">Pending</div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th class="text-center" width="28">No</th>
                    <th>Order ID</th>
                    <th>Cabang</th>
                    <th>Paket</th>
                    <th class="text-center">Cetak</th>
                    <th>Voucher</th>
                    <th class="text-right">Diskon</th>
                    <th class="text-right">Total</th>
                    <th class="text-center">Status</th>
                    <th>Metode</th>
                    <th>Waktu Transaksi</th>
                    <th>Waktu Bayar</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $i => $trx)
                @php
                    $totalPrints = 1 + (int) ($trx->extra_prints ?? 0);
                @endphp
                <tr>
                    <td class="text-center text-muted">{{ $i + 1 }}</td>
                    <td class="fw-bold" style="font-size:9px;">{{ $trx->order_id }}</td>
                    <td>{{ $trx->branch?->name ?? '-' }}</td>
                    <td>{{ $trx->package?->name ?? '-' }}</td>
                    <td class="text-center">{{ $totalPrints }}</td>
                    <td style="font-size:9px;">{{ $trx->voucher?->code ?? '-' }}</td>
                    <td class="text-right">{{ $trx->discount_amount > 0 ? 'Rp '.number_format($trx->discount_amount, 0, ',', '.') : '-' }}</td>
                    <td class="text-right fw-bold">Rp {{ number_format($trx->amount, 0, ',', '.') }}</td>
                    <td class="text-center">
                        <span class="badge badge-{{ $trx->status }}">{{ strtoupper($trx->status) }}</span>
                    </td>
                    <td>{{ $trx->payment_method ? strtoupper($trx->payment_method) : '-' }}</td>
                    <td class="text-muted">{{ $trx->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td class="text-muted">{{ $trx->paid_at?->format('d/m/Y H:i') ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="12" class="text-center text-muted" style="padding:20px;">Tidak ada data transaksi.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-left">
            Dokumen ini dibuat secara otomatis oleh sistem <strong>{{ $siteName }}</strong>
        </div>
        <div class="footer-right">
            Halaman 1 dari 1 &mdash; {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

</body>
</html>
