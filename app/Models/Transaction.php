<?php

namespace App\Models;

use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'order_id', 'branch_id', 'package_id', 'voucher_id', 'amount', 'discount_amount', 'original_amount',
    'extra_prints', 'payment_method', 'payment_url', 'qris_string', 'status', 'duitku_reference', 'paid_at', 'expired_at',
])]
class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'discount_amount' => 'decimal:2',
            'original_amount' => 'decimal:2',
            'extra_prints' => 'integer',
            'paid_at' => 'datetime',
            'expired_at' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function photoSession(): HasOne
    {
        return $this->hasOne(PhotoSession::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Idempotent transition to "paid". Increments the linked voucher's
     * uses_count exactly once across concurrent callbacks/polls.
     */
    public function markAsPaid(?\DateTimeInterface $paidAt = null): bool
    {
        if ($this->isPaid()) {
            return false;
        }

        return DB::transaction(function () use ($paidAt) {
            $affected = static::where('id', $this->id)
                ->where('status', '!=', 'paid')
                ->update([
                    'status' => 'paid',
                    'paid_at' => $paidAt ?? now(),
                ]);

            if ($affected === 0) {
                return false;
            }

            if ($this->voucher_id) {
                Voucher::where('id', $this->voucher_id)->increment('uses_count');
            }

            $this->refresh();

            return true;
        });
    }
}
