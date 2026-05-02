<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Revenue — {{ $siteName }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            background: #ffffff;
        }

        /* ── Header ── */
        .header {
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 60%, #3b82f6 100%);
            color: #fff;
            padding: 28px 36px;
            margin-bottom: 0;
        }

        .header-inner {
            display: table;
            width: 100%;
        }

        .header-left {
            display: table-cell;
            vertical-align: middle;
            width: 60%;
        }

        .header-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 40%;
        }

        .company-name {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.3px;
            color: #fff;
        }

        .doc-title {
            font-size: 13px;
            color: rgba(255,255,255,0.8);
            margin-top: 4px;
            font-weight: 400;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .header-meta {
            font-size: 10px;
            color: rgba(255,255,255,0.75);
            line-height: 1.7;
            text-align: right;
        }

        .header-meta strong {
            color: #fff;
            font-weight: 600;
        }

        /* ── Blue accent bar ── */
        .accent-bar {
            background: #1e40af;
            height: 4px;
        }

        /* ── Wrapper ── */
        .content {
            padding: 24px 36px;
        }

        /* ── Summary Info Bar ── */
        .info-bar {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 16px;
            margin-bottom: 18px;
            display: table;
            width: 100%;
        }

        .info-bar-item {
            display: table-cell;
            vertical-align: middle;
            font-size: 10px;
            color: #374151;
            padding-right: 20px;
        }

        .info-bar-item strong {
            color: #1d4ed8;
            font-weight: 700;
        }

        .info-bar-item .val {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            display: block;
            margin-top: 1px;
        }

        .info-bar-sep {
            display: table-cell;
            vertical-align: middle;
            width: 1px;
            background: #e2e8f0;
            padding: 0 12px;
        }

        /* ── Filter Info ── */
        .filter-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 18px;
            font-size: 10px;
            color: #1d4ed8;
        }

        .filter-info strong { font-weight: 700; }

        /* ── Section title ── */
        .section-title {
            font-size: 12px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
            padding-bottom: 6px;
            border-bottom: 2px solid #e2e8f0;
        }

        /* ── Table ── */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        thead tr {
            background: #1d4ed8;
            color: #ffffff;
        }

        thead th {
            padding: 9px 10px;
            text-align: left;
            font-weight: 700;
            font-size: 9.5px;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }

        thead th.text-right { text-align: right; }
        thead th.text-center { text-align: center; }

        tbody tr:nth-child(even) { background: #f0f4ff; }
        tbody tr:nth-child(odd) { background: #ffffff; }

        tbody td {
            padding: 8px 10px;
            border-bottom: 1px solid #e8edf5;
            vertical-align: middle;
            color: #374151;
        }

        tbody td.text-right { text-align: right; }
        tbody td.text-center { text-align: center; }
        tbody td.mono { font-family: 'Courier New', monospace; font-size: 9.5px; }
        tbody td.amount { font-weight: 700; color: #15803d; text-align: right; }
        tbody td.no-col { color: #9ca3af; text-align: center; }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-paid { background: #dcfce7; color: #16a34a; }

        /* ── Total Row ── */
        tfoot tr {
            background: #1e40af;
            color: #fff;
        }

        tfoot td {
            padding: 10px;
            font-weight: 700;
        }

        tfoot td.text-right { text-align: right; font-size: 12px; }

        /* ── Empty State ── */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #94a3b8;
            font-size: 12px;
        }

        /* ── Footer ── */
        .footer {
            margin-top: 24px;
            padding-top: 14px;
            border-top: 1px solid #e2e8f0;
            display: table;
            width: 100%;
            font-size: 9px;
            color: #94a3b8;
        }

        .footer-left { display: table-cell; vertical-align: middle; }
        .footer-right { display: table-cell; text-align: right; vertical-align: middle; }

        .page-break { page-break-after: always; }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <div class="header-inner">
            <div class="header-left">
                <div class="company-name">{{ $siteName }}</div>
                <div class="doc-title">Laporan Revenue</div>
            </div>
            <div class="header-right">
                <div class="header-meta">
                    @if($filterBranch)
                        <div><strong>Cabang:</strong> {{ $filterBranch }}</div>
                    @else
                        <div><strong>Cabang:</strong> Semua Cabang</div>
                    @endif
                    @if($filterStartDate || $filterEndDate)
                        <div>
                            <strong>Periode:</strong>
                            {{ $filterStartDate ? \Carbon\Carbon::parse($filterStartDate)->format('d M Y') : '—' }}
                            s/d
                            {{ $filterEndDate ? \Carbon\Carbon::parse($filterEndDate)->format('d M Y') : '—' }}
                        </div>
                    @elseif($filterYear)
                        <div><strong>Tahun:</strong> {{ $filterYear }}</div>
                    @endif
                    <div><strong>Dicetak:</strong> {{ now()->format('d M Y, H:i') }}</div>
                    <div><strong>Total Data:</strong> {{ number_format($transactions->count()) }} transaksi</div>
                </div>
            </div>
        </div>
    </div>
    <div class="accent-bar"></div>

    <div class="content">

        {{-- Summary Info Bar --}}
        <div class="info-bar">
            <div class="info-bar-item">
                <strong>TOTAL REVENUE</strong>
                <span class="val">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
            </div>
            <div class="info-bar-item">
                <strong>RATA-RATA / TRANSAKSI</strong>
                <span class="val">Rp {{ number_format($transactions->count() > 0 ? $totalRevenue / $transactions->count() : 0, 0, ',', '.') }}</span>
            </div>
            <div class="info-bar-item">
                <strong>TOTAL TRANSAKSI</strong>
                <span class="val">{{ number_format($transactions->count()) }} transaksi</span>
            </div>
        </div>

        {{-- Table --}}
        <div class="section-title">Detail Transaksi</div>

        @if($transactions->isEmpty())
            <div class="empty-state">
                Tidak ada data transaksi pada periode yang dipilih.
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th class="text-center" style="width: 28px;">No.</th>
                        <th style="width: 130px;">ID Transaksi</th>
                        <th style="width: 78px;">Tanggal</th>
                        <th>Cabang</th>
                        <th>Paket</th>
                        <th class="text-center" style="width: 38px;">Cetak</th>
                        <th style="width: 70px;">Voucher</th>
                        <th class="text-right" style="width: 70px;">Diskon</th>
                        <th>Metode</th>
                        <th class="text-center" style="width: 42px;">Status</th>
                        <th class="text-right" style="width: 88px;">Jumlah (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transactions as $i => $tx)
                        @php $totalPrints = 1 + (int) ($tx->extra_prints ?? 0); @endphp
                        <tr>
                            <td class="no-col">{{ $i + 1 }}</td>
                            <td class="mono">{{ $tx->order_id }}</td>
                            <td>{{ $tx->paid_at?->format('d/m/Y') ?? '-' }}<br><span style="color:#94a3b8;font-size:8.5px;">{{ $tx->paid_at?->format('H:i') }}</span></td>
                            <td>{{ $tx->branch?->name ?? '-' }}</td>
                            <td>{{ $tx->package?->name ?? '-' }}</td>
                            <td class="text-center">{{ $totalPrints }}</td>
                            <td class="mono" style="font-size:8.5px;">{{ $tx->voucher?->code ?? '-' }}</td>
                            <td class="text-right">{{ $tx->discount_amount > 0 ? number_format($tx->discount_amount, 0, ',', '.') : '-' }}</td>
                            <td>{{ strtoupper($tx->payment_method ?? '-') }}</td>
                            <td class="text-center"><span class="badge badge-paid">PAID</span></td>
                            <td class="amount">{{ number_format($tx->amount, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="7" style="font-size:11px; padding-left: 12px;">TOTAL DISKON</td>
                        <td class="text-right" style="font-weight:700;color:#92400e;">{{ number_format($totalDiscount, 0, ',', '.') }}</td>
                        <td colspan="2" style="font-size:11px; padding-left: 12px;">TOTAL REVENUE</td>
                        <td class="text-right">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        @endif

        {{-- Footer --}}
        <div class="footer">
            <div class="footer-left">
                {{ $siteName }} &mdash; Dokumen ini digenerate otomatis oleh sistem.
            </div>
            <div class="footer-right">
                Halaman 1 dari 1 &bull; {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>

    </div>
</body>
</html>
