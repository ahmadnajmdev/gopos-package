<?php

namespace Gopos\Models;

use Gopos\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankReconciliation extends Model
{
    use Auditable;

    protected $fillable = [
        'bank_account_id',
        'reconciliation_number',
        'statement_date',
        'statement_start_date',
        'statement_end_date',
        'statement_balance',
        'book_balance',
        'adjusted_book_balance',
        'difference',
        'status',
        'created_by',
        'completed_by',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'statement_date' => 'date',
        'statement_start_date' => 'date',
        'statement_end_date' => 'date',
        'statement_balance' => 'decimal:2',
        'book_balance' => 'decimal:2',
        'adjusted_book_balance' => 'decimal:2',
        'difference' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public const STATUS_DRAFT = 'draft';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BankReconciliationItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * Generate reconciliation number.
     */
    public static function generateNumber(): string
    {
        $lastNumber = static::selectRaw('MAX(CAST(SUBSTRING(reconciliation_number, 5) AS UNSIGNED)) as max_num')
            ->value('max_num');
        $nextNumber = ($lastNumber ?? 0) + 1;

        return 'REC-'.str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate adjusted book balance.
     */
    public function calculateAdjustedBalance(): float
    {
        $adjustments = $this->items()->sum('amount');

        return $this->book_balance + $adjustments;
    }

    /**
     * Calculate difference.
     */
    public function calculateDifference(): float
    {
        $adjusted = $this->adjusted_book_balance ?? $this->calculateAdjustedBalance();

        return $this->statement_balance - $adjusted;
    }

    /**
     * Check if reconciliation is balanced.
     */
    public function isBalanced(): bool
    {
        return abs($this->calculateDifference()) < 0.01;
    }

    /**
     * Get outstanding checks total.
     */
    public function getOutstandingChecksTotal(): float
    {
        return $this->items()
            ->where('type', 'outstanding_check')
            ->where('is_reconciled', false)
            ->sum('amount');
    }

    /**
     * Get deposits in transit total.
     */
    public function getDepositsInTransitTotal(): float
    {
        return $this->items()
            ->where('type', 'deposit_in_transit')
            ->where('is_reconciled', false)
            ->sum('amount');
    }

    /**
     * Complete the reconciliation.
     */
    public function complete(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_by' => auth()->id(),
            'completed_at' => now(),
            'adjusted_book_balance' => $this->calculateAdjustedBalance(),
            'difference' => $this->calculateDifference(),
        ]);

        // Mark all items as reconciled
        $this->items()->update(['is_reconciled' => true]);

        // Mark associated bank transactions as reconciled
        $transactionIds = $this->items()
            ->whereNotNull('bank_transaction_id')
            ->pluck('bank_transaction_id');

        BankTransaction::whereIn('id', $transactionIds)
            ->update(['status' => BankTransaction::STATUS_RECONCILED]);

        // Update bank account
        $this->bankAccount->update([
            'last_reconciled_date' => $this->statement_date,
            'last_reconciled_balance' => $this->statement_balance,
        ]);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->reconciliation_number)) {
                $model->reconciliation_number = static::generateNumber();
            }
            if (empty($model->created_by)) {
                $model->created_by = auth()->id();
            }
        });
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }
}
