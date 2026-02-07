<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankReconciliationItem extends Model
{
    protected $fillable = [
        'bank_reconciliation_id',
        'bank_transaction_id',
        'type',
        'description',
        'amount',
        'is_reconciled',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_reconciled' => 'boolean',
    ];

    public const TYPE_OUTSTANDING_CHECK = 'outstanding_check';

    public const TYPE_DEPOSIT_IN_TRANSIT = 'deposit_in_transit';

    public const TYPE_BANK_CHARGE = 'bank_charge';

    public const TYPE_BANK_INTEREST = 'bank_interest';

    public const TYPE_ERROR = 'error';

    public const TYPE_ADJUSTMENT = 'adjustment';

    public function reconciliation(): BelongsTo
    {
        return $this->belongsTo(BankReconciliation::class, 'bank_reconciliation_id');
    }

    public function bankTransaction(): BelongsTo
    {
        return $this->belongsTo(BankTransaction::class);
    }

    /**
     * Check if this is a book adjustment (affects book balance).
     */
    public function isBookAdjustment(): bool
    {
        return in_array($this->type, [
            self::TYPE_BANK_CHARGE,
            self::TYPE_BANK_INTEREST,
            self::TYPE_ERROR,
            self::TYPE_ADJUSTMENT,
        ]);
    }

    /**
     * Check if this is an outstanding item.
     */
    public function isOutstandingItem(): bool
    {
        return in_array($this->type, [
            self::TYPE_OUTSTANDING_CHECK,
            self::TYPE_DEPOSIT_IN_TRANSIT,
        ]);
    }

    public function scopeOutstanding($query)
    {
        return $query->whereIn('type', [
            self::TYPE_OUTSTANDING_CHECK,
            self::TYPE_DEPOSIT_IN_TRANSIT,
        ]);
    }

    public function scopeBookAdjustments($query)
    {
        return $query->whereIn('type', [
            self::TYPE_BANK_CHARGE,
            self::TYPE_BANK_INTEREST,
            self::TYPE_ERROR,
            self::TYPE_ADJUSTMENT,
        ]);
    }
}
