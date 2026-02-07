<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankTransaction extends Model
{
    protected $fillable = [
        'bank_account_id',
        'journal_entry_id',
        'type',
        'reference',
        'description',
        'amount',
        'balance_after',
        'transaction_date',
        'status',
        'payee',
        'check_number',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    public const TYPE_DEPOSIT = 'deposit';

    public const TYPE_WITHDRAWAL = 'withdrawal';

    public const TYPE_TRANSFER = 'transfer';

    public const TYPE_FEE = 'fee';

    public const TYPE_INTEREST = 'interest';

    public const TYPE_OTHER = 'other';

    public const STATUS_PENDING = 'pending';

    public const STATUS_CLEARED = 'cleared';

    public const STATUS_RECONCILED = 'reconciled';

    public const STATUS_VOID = 'void';

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if transaction is a debit (reduces balance).
     */
    public function isDebit(): bool
    {
        return in_array($this->type, [self::TYPE_WITHDRAWAL, self::TYPE_FEE, self::TYPE_TRANSFER]);
    }

    /**
     * Check if transaction is a credit (increases balance).
     */
    public function isCredit(): bool
    {
        return in_array($this->type, [self::TYPE_DEPOSIT, self::TYPE_INTEREST]);
    }

    /**
     * Get formatted amount with sign.
     */
    public function getSignedAmountAttribute(): float
    {
        return $this->isDebit() ? -abs($this->amount) : abs($this->amount);
    }

    /**
     * Mark as cleared.
     */
    public function markCleared(): void
    {
        $this->update(['status' => self::STATUS_CLEARED]);
    }

    /**
     * Mark as reconciled.
     */
    public function markReconciled(): void
    {
        $this->update(['status' => self::STATUS_RECONCILED]);
    }

    /**
     * Void the transaction.
     */
    public function void(): void
    {
        $this->update(['status' => self::STATUS_VOID]);
        $this->bankAccount->updateBalance();
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeCleared($query)
    {
        return $query->where('status', self::STATUS_CLEARED);
    }

    public function scopeUnreconciled($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_CLEARED]);
    }

    public function scopeReconciled($query)
    {
        return $query->where('status', self::STATUS_RECONCILED);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }
}
