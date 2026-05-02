<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['branch_id', 'purpose', 'name', 'connector', 'device', 'profile', 'is_active'])]
class Printer extends Model
{
    public const PURPOSE_VOUCHER = 'voucher';

    public const PURPOSE_PHOTO = 'photo';

    public const CONNECTOR_FILE = 'file';

    public const CONNECTOR_CUPS = 'cups';

    public const CONNECTOR_NETWORK = 'network';

    /**
     * @return array<string, string>
     */
    public static function purposeLabels(): array
    {
        return [
            self::PURPOSE_VOUCHER => 'Voucher Thermal',
            self::PURPOSE_PHOTO => 'Foto Photobooth',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function connectorLabels(): array
    {
        return [
            self::CONNECTOR_FILE => 'File / Serial Device (USB / Bluetooth)',
            self::CONNECTOR_CUPS => 'CUPS Printer (sistem macOS / Linux)',
            self::CONNECTOR_NETWORK => 'Network Printer (TCP/IP)',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function profileOptions(): array
    {
        return [
            'simple' => 'Simple (default, kompatibel umum)',
            'default' => 'Default (Epson modern)',
            'TM-T88III' => 'Epson TM-T88III',
            'TM-T88IV' => 'Epson TM-T88IV',
            'TM-T20' => 'Epson TM-T20',
            'P-822D' => 'P-822D',
            'POS-5890' => 'POS-5890 (mini 58mm)',
            'POS-8360' => 'POS-8360 (mini 80mm)',
        ];
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function purposeLabel(): string
    {
        return self::purposeLabels()[$this->purpose] ?? ucfirst((string) $this->purpose);
    }

    public function connectorLabel(): string
    {
        return self::connectorLabels()[$this->connector] ?? strtoupper((string) $this->connector);
    }
}
