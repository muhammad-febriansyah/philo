<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $printTitle }}</title>
    @php $embedded = request()->boolean('embedded'); @endphp
    <style>
        :root {
            --paper-width: 76mm;
            --ink: #111827;
            --muted: #6b7280;
            --line: #d1d5db;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 16px;
            font-family: Arial, Helvetica, sans-serif;
            color: var(--ink);
            background: #f3f4f6;
        }

        .toolbar {
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }

        .toolbar button,
        .toolbar a {
            border: 0;
            border-radius: 10px;
            padding: 10px 14px;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
        }

        .toolbar button {
            background: #111827;
            color: #fff;
        }

        .toolbar a {
            background: #e5e7eb;
            color: #111827;
        }

        .print-list {
            display: grid;
            gap: 16px;
            justify-content: center;
        }

        .slip {
            width: var(--paper-width);
            background: #fff;
            padding: 12px 10px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .slip + .slip {
            page-break-before: always;
        }

        .brand,
        .footer-note {
            text-align: center;
        }

        .brand-title {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .brand-sub,
        .footer-note {
            font-size: 11px;
            color: var(--muted);
            line-height: 1.45;
        }

        .divider {
            border-top: 1px dashed var(--line);
            margin: 10px 0;
        }

        .voucher-name {
            font-size: 15px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 6px;
        }

        .voucher-code {
            font-size: 24px;
            font-weight: 800;
            text-align: center;
            letter-spacing: 0.12em;
            padding: 6px 0;
        }

        .voucher-value {
            text-align: center;
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .qr-wrap {
            display: flex;
            justify-content: center;
            margin: 6px 0 10px;
        }

        .qr-wrap img {
            width: 145px;
            height: 145px;
            display: block;
        }

        .meta {
            font-size: 11px;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            padding: 3px 0;
            border-bottom: 1px dashed #ececec;
        }

        .meta-row:last-child {
            border-bottom: 0;
        }

        .meta-label {
            color: var(--muted);
        }

        .meta-value {
            font-weight: 700;
            text-align: right;
        }

        @media print {
            @page {
                size: 80mm auto;
                margin: 4mm;
            }

            body {
                background: #fff;
                padding: 0;
            }

            .toolbar {
                display: none !important;
            }

            .slip {
                width: 100%;
                border: 0;
                box-shadow: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    @unless($embedded)
        <div class="toolbar">
            <button type="button" onclick="window.print()">Print Sekarang</button>
            <a href="{{ route('vouchers.index') }}">Kembali ke Voucher</a>
        </div>
    @endunless

    <div class="print-list">
        @foreach($vouchers as $voucher)
            <section class="slip">
                <div class="brand">
                    <div class="brand-title">Philo Voucher</div>
                    <div class="brand-sub">Voucher thermal siap scan / input manual</div>
                </div>

                <div class="divider"></div>

                <div class="voucher-name">{{ $voucher['name'] }}</div>
                <div class="voucher-code">{{ $voucher['code'] }}</div>
                <div class="voucher-value">Diskon {{ $voucher['value_formatted'] }}</div>

                <div class="qr-wrap">
                    <img src="{{ $voucher['qr_svg'] }}" alt="QR {{ $voucher['code'] }}">
                </div>

                <div class="meta">
                    <div class="meta-row">
                        <span class="meta-label">Minimum belanja</span>
                        <span class="meta-value">{{ $voucher['min_purchase_formatted'] }}</span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label">Periode</span>
                        <span class="meta-value">{{ $voucher['validity'] }}</span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label">Status</span>
                        <span class="meta-value">{{ $voucher['status_label'] }}</span>
                    </div>
                </div>

                <div class="divider"></div>

                <div class="footer-note">
                    Simpan struk ini dan gunakan kode voucher saat transaksi.
                </div>
            </section>
        @endforeach
    </div>
</body>
<script>
    window.addEventListener('load', function () {
        setTimeout(function () {
            window.print();
        }, 180);
    });
</script>
</html>
