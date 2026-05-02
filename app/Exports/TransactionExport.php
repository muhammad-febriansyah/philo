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

class TransactionExport implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStyles, WithTitle
{
    private int $rowIndex = 0;

    public function __construct(
        private readonly ?int $branchId,
        private readonly ?string $status,
        private readonly ?string $search,
        private readonly ?string $startDate = null,
        private readonly ?string $endDate = null,
    ) {}

    public function query()
    {
        return Transaction::with(['branch', 'package', 'voucher:id,code'])
            ->when($this->branchId, fn ($q) => $q->where('branch_id', $this->branchId))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->startDate, fn ($q) => $q->whereDate('created_at', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('created_at', '<=', $this->endDate))
            ->when($this->search, function ($q) {
                $kw = $this->search;
                $q->where(function ($inner) use ($kw) {
                    $inner->where('order_id', 'like', "%{$kw}%")
                        ->orWhereHas('branch', fn ($b) => $b->where('name', 'like', "%{$kw}%"))
                        ->orWhereHas('package', fn ($p) => $p->where('name', 'like', "%{$kw}%"));
                });
            })
            ->orderByDesc('created_at');
    }

    public function title(): string
    {
        return 'Data Transaksi';
    }

    public function headings(): array
    {
        return ['No.', 'Order ID', 'Cabang', 'Paket', 'Cetak', 'Voucher', 'Diskon (Rp)', 'Total (Rp)', 'Status', 'Metode Bayar', 'Referensi', 'Waktu Transaksi', 'Waktu Bayar'];
    }

    /** @param Transaction $row */
    public function map($row): array
    {
        $this->rowIndex++;

        $statusMap = [
            'pending' => 'PENDING',
            'paid' => 'PAID',
            'expired' => 'EXPIRED',
            'failed' => 'FAILED',
            'cancelled' => 'CANCELLED',
        ];

        $totalPrints = 1 + (int) ($row->extra_prints ?? 0);

        return [
            $this->rowIndex,
            $row->order_id,
            $row->branch?->name ?? '-',
            $row->package?->name ?? '-',
            $totalPrints.' lembar',
            $row->voucher?->code ?? '-',
            (int) ($row->discount_amount ?? 0),
            $row->amount,
            $statusMap[$row->status] ?? strtoupper($row->status),
            $row->payment_method ? strtoupper($row->payment_method) : '-',
            $row->duitku_reference ?? '-',
            $row->created_at?->format('d/m/Y H:i') ?? '-',
            $row->paid_at?->format('d/m/Y H:i') ?? '-',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'G' => '#,##0',
            'H' => '#,##0',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $sheet->getHighestRow();

        // Header — warna kuning tema
        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FF1a1200'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFC9A800']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFa88e00']]],
        ]);

        // Data rows
        for ($row = 2; $row <= $lastRow; $row++) {
            $fill = $row % 2 === 0 ? 'FFFFF9e0' : 'FFFFFFFF';
            $sheet->getStyle("A{$row}:M{$row}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $fill]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFe5e0c0']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
        }

        // Alignment columns
        $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("E2:E{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("G2:H{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("I2:I{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getDefaultRowDimension()->setRowHeight(22);
        $sheet->getRowDimension(1)->setRowHeight(28);

        return [];
    }
}
