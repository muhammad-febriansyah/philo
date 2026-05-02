<?php

namespace App\Models;

use Database\Factories\VoucherFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'code', 'name', 'type', 'value', 'max_uses', 'uses_count',
    'min_purchase', 'valid_from', 'valid_until',
    'applicable_packages', 'applicable_branches',
    'is_active', 'source', 'created_by',
])]
class Voucher extends Model
{
    /** @use HasFactory<VoucherFactory> */
    use HasFactory;

    public const TYPE_PERCENTAGE = 'percentage';

    public const TYPE_FIXED = 'fixed';

    public const SOURCE_MANUAL = 'manual';

    public const SOURCE_BULK = 'bulk';

    public const SOURCE_AUTO_NEXT_VISIT = 'auto_next_visit';

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_purchase' => 'decimal:2',
            'max_uses' => 'integer',
            'uses_count' => 'integer',
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
            'applicable_packages' => 'array',
            'applicable_branches' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        $now = now();

        return $query->where('is_active', true)
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
            })
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', $now);
            });
    }

    public function isExpired(): bool
    {
        return $this->valid_until && $this->valid_until->isPast();
    }

    public function isNotYetActive(): bool
    {
        return $this->valid_from && $this->valid_from->isFuture();
    }

    public function isExhausted(): bool
    {
        return $this->max_uses !== null && $this->uses_count >= $this->max_uses;
    }

    public function isUsableFor(?int $packageId = null, ?int $branchId = null, ?float $purchaseAmount = null): bool
    {
        if (! $this->is_active || $this->isExpired() || $this->isNotYetActive() || $this->isExhausted()) {
            return false;
        }

        if ($this->min_purchase !== null && $purchaseAmount !== null && $purchaseAmount < (float) $this->min_purchase) {
            return false;
        }

        if (! empty($this->applicable_packages) && $packageId !== null && ! in_array($packageId, $this->applicable_packages)) {
            return false;
        }

        if (! empty($this->applicable_branches) && $branchId !== null && ! in_array($branchId, $this->applicable_branches)) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(float $amount): float
    {
        $discount = $this->type === self::TYPE_PERCENTAGE
            ? $amount * ((float) $this->value / 100)
            : (float) $this->value;

        return (float) min($discount, $amount);
    }

    public function statusLabel(): string
    {
        if (! $this->is_active) {
            return 'Nonaktif';
        }
        if ($this->isExpired()) {
            return 'Kedaluwarsa';
        }
        if ($this->isNotYetActive()) {
            return 'Belum Aktif';
        }
        if ($this->isExhausted()) {
            return 'Habis Terpakai';
        }

        return 'Aktif';
    }

    public static function generateUniqueCode(string $prefix = '', int $length = 8): string
    {
        do {
            $code = strtoupper($prefix.Str::random($length));
        } while (self::where('code', $code)->exists());

        return $code;
    }
}
