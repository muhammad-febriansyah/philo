<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RevenueExport implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        private readonly ?int $branchId,
        private readonly ?string $startDate,
        private readonly ?string $endDate,
        private readonly ?int $year,
    ) {}

    public function query()
    {
        return Transaction::with(['branch', 'package', 'voucher:id,code'])
            ->where('status', 'paid')
            ->when($this->branchId, fn ($q) => $q->where('branch_id', $this->branchId))
            ->when($this->startDate, fn ($q) => $q->whereDate('paid_at', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('paid_at', '<=', $this->endDate))
            ->when($this->year && ! $this->startDate && ! $this->endDate, fn ($q) => $q->whereYear('paid_at', $this->year))
            ->orderByDesc('paid_at');
    }

    public function title(): string
    {
        return 'Laporan Revenue';
    }

    public function headings(): array
    {
        return [
            'No.',
            'ID Transaksi',
            'Tanggal Bayar',
            'Cabang',
            'Paket',
            'Cetak',
            'Voucher',
            'Diskon (Rp)',
            'Metode Pembayaran',
            'Status',
            'Jumlah (Rp)',
        ];
    }

    /** @param Transaction $row */
    public function map($row): array
    {
        static $i = 0;
        $i++;

        $totalPrints = 1 + (int) ($row->extra_prints ?? 0);

        return [
            $i,
            $row->order_id,
            $row->paid_at?->format('d/m/Y H:i') ?? '-',
            $row->branch?->name ?? '-',
            $row->package?->name ?? '-',
            $totalPrints.' lembar',
            $row->voucher?->code ?? '-',
            (int) ($row->discount_amount ?? 0),
            $row->payment_method ?? '-',
            'PAID',
            $row->amount,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'H' => '#,##0',
            'K' => '#,##0',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $sheet->getHighestRow();

        // Header row styling
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1d4ed8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFbfdbfe']]],
        ]);

        // Data rows alternating color + border
        for ($row = 2; $row <= $lastRow; $row++) {
            $fill = $row % 2 === 0 ? 'FFf0f4ff' : 'FFFFFFFF';
            $sheet->getStyle("A{$row}:K{$row}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $fill]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFe2e8f0']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
        }

        // Amount + Diskon columns right-align
        $sheet->getStyle("H2:H{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("K2:K{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // No. + Cetak column center
        $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("F2:F{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Row height
        $sheet->getDefaultRowDimension()->setRowHeight(22);
        $sheet->getRowDimension(1)->setRowHeight(28);

        return [];
    }
}
