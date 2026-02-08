<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_type_id',
        'parent_id',
        'currency_id',
        'code',
        'name',
        'name_ar',
        'name_ckb',
        'description',
        'is_active',
        'is_system',
        'opening_balance',
        'current_balance',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
    ];

    // Relationships

    public function accountType(): BelongsTo
    {
        return $this->belongsTo(AccountType::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $typeId)
    {
        return $query->where('account_type_id', $typeId);
    }

    public function scopeRootAccounts($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeAssets($query)
    {
        return $query->whereHas('accountType', fn ($q) => $q->where('name', 'Asset'));
    }

    public function scopeLiabilities($query)
    {
        return $query->whereHas('accountType', fn ($q) => $q->where('name', 'Liability'));
    }

    public function scopeEquity($query)
    {
        return $query->whereHas('accountType', fn ($q) => $q->where('name', 'Equity'));
    }

    public function scopeRevenue($query)
    {
        return $query->whereHas('accountType', fn ($q) => $q->where('name', 'Revenue'));
    }

    public function scopeExpenses($query)
    {
        return $query->whereHas('accountType', fn ($q) => $q->where('name', 'Expense'));
    }

    // Methods

    /**
     * Get the full account code including parent codes
     */
    public function getFullCodeAttribute(): string
    {
        if ($this->parent) {
            return $this->parent->full_code.'-'.$this->code;
        }

        return $this->code;
    }

    /**
     * Get localized name based on current locale
     */
    public function getLocalizedNameAttribute(): string
    {
        $locale = app()->getLocale();

        if ($locale === 'ar' && ! empty($this->name_ar)) {
            return $this->name_ar;
        }

        if ($locale === 'ckb' && ! empty($this->name_ckb)) {
            return $this->name_ckb;
        }

        return $this->name;
    }

    /**
     * Get display name with code
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->code.' - '.$this->localized_name;
    }

    /**
     * Check if account has a debit normal balance
     */
    public function isDebitBalance(): bool
    {
        return $this->accountType?->isDebitBalance() ?? false;
    }

    /**
     * Check if account has a credit normal balance
     */
    public function isCreditBalance(): bool
    {
        return $this->accountType?->isCreditBalance() ?? false;
    }

    /**
     * Calculate the current balance from journal entries
     */
    public function calculateBalance(): float
    {
        $lines = $this->journalLines()
            ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted'))
            ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->first();

        $totalDebit = $lines->total_debit ?? 0;
        $totalCredit = $lines->total_credit ?? 0;

        // For debit-normal accounts (Assets, Expenses): balance = debits - credits
        // For credit-normal accounts (Liabilities, Equity, Revenue): balance = credits - debits
        if ($this->isDebitBalance()) {
            return $this->opening_balance + $totalDebit - $totalCredit;
        } else {
            return $this->opening_balance + $totalCredit - $totalDebit;
        }
    }

    /**
     * Get balance for a specific period
     */
    public function getBalanceForPeriod(string $startDate, string $endDate): float
    {
        $lines = $this->journalLines()
            ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                $q->where('status', 'posted')
                    ->whereBetween('entry_date', [$startDate, $endDate]);
            })
            ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->first();

        $totalDebit = $lines->total_debit ?? 0;
        $totalCredit = $lines->total_credit ?? 0;

        if ($this->isDebitBalance()) {
            return $totalDebit - $totalCredit;
        } else {
            return $totalCredit - $totalDebit;
        }
    }

    /**
     * Update the current balance
     */
    public function updateBalance(): void
    {
        $this->current_balance = $this->calculateBalance();
        $this->save();
    }

    /**
     * Check if account can be deleted
     */
    public function canBeDeleted(): bool
    {
        if ($this->is_system) {
            return false;
        }

        if ($this->journalLines()->exists()) {
            return false;
        }

        if ($this->children()->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Get all descendant accounts recursively
     */
    public function getAllDescendants(): \Illuminate\Support\Collection
    {
        $descendants = collect();

        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getAllDescendants());
        }

        return $descendants;
    }
}
