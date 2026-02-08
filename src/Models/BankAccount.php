<?php

namespace Gopos\Models;

use Gopos\Models\Concerns\Auditable;
use Gopos\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    use Auditable;
    use BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'account_id',
        'bank_name',
        'bank_name_ar',
        'bank_name_ckb',
        'account_number',
        'iban',
        'swift_code',
        'branch',
        'currency_id',
        'opening_balance',
        'current_balance',
        'last_reconciled_date',
        'last_reconciled_balance',
        'is_active',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'last_reconciled_date' => 'date',
        'last_reconciled_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class);
    }

    public function reconciliations(): HasMany
    {
        return $this->hasMany(BankReconciliation::class);
    }

    /**
     * Get localized bank name.
     */
    public function getLocalizedNameAttribute(): string
    {
        $locale = app()->getLocale();

        if ($locale === 'ar' && ! empty($this->bank_name_ar)) {
            return $this->bank_name_ar;
        }

        if ($locale === 'ckb' && ! empty($this->bank_name_ckb)) {
            return $this->bank_name_ckb;
        }

        return $this->bank_name;
    }

    /**
     * Get display name.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->localized_name.' - '.$this->account_number;
    }

    /**
     * Get unreconciled balance.
     */
    public function getUnreconciledBalanceAttribute(): float
    {
        $unreconciledTransactions = $this->transactions()
            ->whereIn('status', ['pending', 'cleared'])
            ->sum('amount');

        return $this->last_reconciled_balance + $unreconciledTransactions;
    }

    /**
     * Get pending transactions count.
     */
    public function getPendingTransactionsCountAttribute(): int
    {
        return $this->transactions()->where('status', 'pending')->count();
    }

    /**
     * Update current balance.
     */
    public function updateBalance(): void
    {
        $lastTransaction = $this->transactions()
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        $this->current_balance = $lastTransaction?->balance_after ?? $this->opening_balance;
        $this->save();
    }

    /**
     * Record a transaction.
     */
    public function recordTransaction(array $data): BankTransaction
    {
        $amount = $data['amount'];
        $type = $data['type'];

        // Calculate balance after
        $balanceAfter = $this->current_balance;
        if (in_array($type, ['deposit', 'interest'])) {
            $balanceAfter += abs($amount);
        } else {
            $balanceAfter -= abs($amount);
        }

        $transaction = $this->transactions()->create([
            'type' => $type,
            'reference' => $data['reference'] ?? null,
            'description' => $data['description'],
            'amount' => $amount,
            'balance_after' => $balanceAfter,
            'transaction_date' => $data['transaction_date'] ?? now(),
            'status' => $data['status'] ?? 'pending',
            'payee' => $data['payee'] ?? null,
            'check_number' => $data['check_number'] ?? null,
            'journal_entry_id' => $data['journal_entry_id'] ?? null,
            'created_by' => auth()->id(),
        ]);

        $this->update(['current_balance' => $balanceAfter]);

        return $transaction;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
