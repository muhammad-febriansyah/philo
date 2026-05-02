<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Printer;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Mike42\Escpos\CapabilityProfile;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\PrintConnector;
use Mike42\Escpos\Printer as EscPrinter;
use RuntimeException;
use Throwable;

class ThermalPrinterService
{
    public function printerFor(Branch $branch, string $purpose): ?Printer
    {
        return Printer::query()
            ->where('branch_id', $branch->id)
            ->where('purpose', $purpose)
            ->where('is_active', true)
            ->first();
    }

    public function requirePrinter(Branch $branch, string $purpose): Printer
    {
        $printer = $this->printerFor($branch, $purpose);
        if (! $printer) {
            $label = Printer::purposeLabels()[$purpose] ?? $purpose;
            throw new RuntimeException("Printer '{$label}' belum diatur untuk cabang {$branch->name}.");
        }

        return $printer;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function printVoucher(array $payload, Branch $branch): void
    {
        $printer = $this->requirePrinter($branch, Printer::PURPOSE_VOUCHER);
        $this->withPrinter($printer, function (EscPrinter $esc) use ($payload) {
            $this->renderVoucherSlip($esc, $payload);
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $payloads
     */
    public function printVouchers(array $payloads, Branch $branch): int
    {
        if ($payloads === []) {
            return 0;
        }

        $printer = $this->requirePrinter($branch, Printer::PURPOSE_VOUCHER);

        $count = 0;
        $this->withPrinter($printer, function (EscPrinter $esc) use ($payloads, &$count) {
            foreach ($payloads as $payload) {
                $this->renderVoucherSlip($esc, $payload);
                $count++;
            }
        });

        return $count;
    }

    public function printTest(Printer $printer): void
    {
        $this->withPrinter($printer, function (EscPrinter $esc) use ($printer) {
            $esc->initialize();
            $esc->setJustification(EscPrinter::JUSTIFY_CENTER);
            $esc->setEmphasis(true);
            $esc->setTextSize(2, 2);
            $esc->text("TEST PRINT\n");
            $esc->setTextSize(1, 1);
            $esc->setEmphasis(false);
            $esc->text(str_repeat('=', 32)."\n");
            $esc->setJustification(EscPrinter::JUSTIFY_LEFT);
            $esc->text('Cabang  : '.$printer->branch?->name."\n");
            $esc->text('Slot    : '.$printer->purposeLabel()."\n");
            $esc->text('Device  : '.$printer->device."\n");
            $esc->text('Tanggal : '.now()->format('d/m/Y H:i:s')."\n");
            $esc->text('Status  : OK'."\n");
            $esc->text(str_repeat('-', 32)."\n");
            $esc->setJustification(EscPrinter::JUSTIFY_CENTER);
            $esc->text("Printer thermal siap.\n");
            $esc->feed(4);
            $esc->cut();
        });
    }

    /**
     * @param  callable(EscPrinter): void  $callback
     */
    private function withPrinter(Printer $printer, callable $callback): void
    {
        $esc = null;
        $connector = $this->makeConnector($printer);

        try {
            $profile = CapabilityProfile::load($printer->profile ?: 'simple');
            $esc = new EscPrinter($connector, $profile);
            $callback($esc);
        } catch (Throwable $e) {
            Log::error('Thermal print failed', [
                'printer_id' => $printer->id,
                'branch_id' => $printer->branch_id,
                'connector' => $printer->connector,
                'device' => $printer->device,
                'message' => $e->getMessage(),
            ]);
            throw new RuntimeException($this->humanizeError($e), previous: $e);
        } finally {
            if ($esc !== null) {
                try {
                    $esc->close();
                } catch (Throwable) {
                    // already failing — swallow secondary error
                }
            }
        }
    }

    private function makeConnector(Printer $printer): PrintConnector
    {
        return match ($printer->connector) {
            Printer::CONNECTOR_CUPS => new CupsPrintConnector($printer->device),
            Printer::CONNECTOR_NETWORK => $this->makeNetworkConnector($printer->device),
            Printer::CONNECTOR_FILE => new FilePrintConnector($printer->device),
            default => throw new RuntimeException('Tipe koneksi printer tidak dikenal.'),
        };
    }

    private function makeNetworkConnector(string $device): NetworkPrintConnector
    {
        $parts = explode(':', $device, 2);
        $host = trim($parts[0] ?? '');
        $port = isset($parts[1]) ? (int) $parts[1] : 9100;

        if ($host === '') {
            throw new RuntimeException('Host printer jaringan tidak boleh kosong.');
        }

        return new NetworkPrintConnector($host, $port);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function renderVoucherSlip(EscPrinter $esc, array $payload): void
    {
        $esc->initialize();

        $esc->setJustification(EscPrinter::JUSTIFY_CENTER);
        $esc->setEmphasis(true);
        $esc->setTextSize(2, 2);
        $esc->text(strtoupper((string) ($payload['site_name'] ?? $this->siteName()))."\n");
        $esc->setTextSize(1, 1);
        $esc->setEmphasis(false);
        $esc->text("Voucher Diskon\n");
        $esc->text(str_repeat('=', 32)."\n");

        $esc->setEmphasis(true);
        $esc->setTextSize(2, 2);
        $esc->text(((string) ($payload['code'] ?? ''))."\n");
        $esc->setTextSize(1, 1);
        $esc->setEmphasis(false);

        if (! empty($payload['name'])) {
            $esc->text(((string) $payload['name'])."\n");
        }

        $esc->feed(1);

        if (! empty($payload['value_label'])) {
            $esc->setEmphasis(true);
            $esc->text(((string) $payload['value_label'])."\n");
            $esc->setEmphasis(false);
        }

        $esc->text(str_repeat('-', 32)."\n");

        $esc->setJustification(EscPrinter::JUSTIFY_LEFT);
        if (! empty($payload['min_purchase_label'])) {
            $esc->text('Min. Belanja : '.$payload['min_purchase_label']."\n");
        }
        if (! empty($payload['valid_label'])) {
            $esc->text('Berlaku      : '.$payload['valid_label']."\n");
        }
        if (! empty($payload['max_uses_label'])) {
            $esc->text('Pemakaian    : '.$payload['max_uses_label']."\n");
        }

        if (! empty($payload['code'])) {
            $esc->feed(1);
            $esc->setJustification(EscPrinter::JUSTIFY_CENTER);
            $esc->qrCode((string) $payload['code'], EscPrinter::QR_ECLEVEL_L, 5);
        }

        $esc->text(str_repeat('-', 32)."\n");
        $esc->setJustification(EscPrinter::JUSTIFY_CENTER);
        $esc->text("Tukarkan kode di booth\n");
        $esc->text("saat memilih paket foto.\n");
        $esc->feed(1);
        $esc->text("Terima kasih :)\n");
        $esc->feed(3);
        $esc->cut();
    }

    private function siteName(): string
    {
        return (string) (Setting::get('site_name') ?: config('app.name', 'Philo'));
    }

    private function humanizeError(Throwable $e): string
    {
        $message = $e->getMessage();

        if (str_contains($message, 'Cannot initialise FilePrintConnector')) {
            return 'Tidak bisa membuka device printer. Pastikan path device benar dan printer Bluetooth/USB aktif.';
        }

        if (str_contains($message, 'lp:') || str_contains($message, 'CUPS')) {
            return 'CUPS menolak job print. Pastikan nama printer benar (cek `lpstat -p`).';
        }

        if (str_contains($message, 'Connection refused') || str_contains($message, 'php_network_getaddresses')) {
            return 'Tidak bisa konek ke printer jaringan. Cek host/port dan koneksi LAN.';
        }

        return 'Gagal print thermal: '.$message;
    }
}
