<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class JournalEntry extends Model
{
    protected $fillable = [
        'entry_number',
        'entry_date',
        'reference_type',
        'reference_id',
        'description',
        'total_debit',
        'total_credit',
        'currency_id',
        'exchange_rate',
        'status',
        'posted_at',
        'posted_by',
        'voided_at',
        'voided_by',
        'void_reason',
        'created_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'posted_at' => 'datetime',
        'voided_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($entry) {
            if (empty($entry->entry_number)) {
                $entry->entry_number = static::generateEntryNumber();
            }

            if (empty($entry->created_by)) {
                $entry->created_by = auth()->id();
            }
        });
    }

    // Relationships

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo('reference', 'reference_type', 'reference_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function postedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function voidedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    // Scopes

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    public function scopeVoided($query)
    {
        return $query->where('status', 'voided');
    }

    public function scopeForPeriod($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('entry_date', [$startDate, $endDate]);
    }

    // Methods

    /**
     * Generate unique entry number
     */
    public static function generateEntryNumber(): string
    {
        $lastEntry = static::orderBy('id', 'desc')->first();

        $nextNumber = $lastEntry ? ((int) substr($lastEntry->entry_number, 3)) + 1 : 1;

        return 'JE-'.str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Check if entry is balanced (debits = credits)
     */
    public function isBalanced(): bool
    {
        $totals = $this->lines()
            ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->first();

        return abs(($totals->total_debit ?? 0) - ($totals->total_credit ?? 0)) < 0.01;
    }

    /**
     * Update totals from lines
     */
    public function updateTotals(): void
    {
        $totals = $this->lines()
            ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->first();

        $this->update([
            'total_debit' => $totals->total_debit ?? 0,
            'total_credit' => $totals->total_credit ?? 0,
        ]);
    }

    /**
     * Post the journal entry
     */
    public function post(): bool
    {
        if ($this->status !== 'draft') {
            return false;
        }

        if (! $this->isBalanced()) {
            return false;
        }

        $this->update([
            'status' => 'posted',
            'posted_at' => now(),
            'posted_by' => auth()->id(),
        ]);

        // Update account balances
        foreach ($this->lines as $line) {
            $line->account->updateBalance();
        }

        return true;
    }

    /**
     * Void the journal entry
     */
    public function void(string $reason): bool
    {
        if ($this->status !== 'posted') {
            return false;
        }

        $this->update([
            'status' => 'voided',
            'voided_at' => now(),
            'voided_by' => auth()->id(),
            'void_reason' => $reason,
        ]);

        // Update account balances
        foreach ($this->lines as $line) {
            $line->account->updateBalance();
        }

        return true;
    }

    /**
     * Check if entry can be voided
     */
    public function canBeVoided(): bool
    {
        return $this->status === 'posted';
    }

    /**
     * Check if entry can be edited
     */
    public function canBeEdited(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if entry can be deleted
     */
    public function canBeDeleted(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'warning',
            'posted' => 'success',
            'voided' => 'danger',
            default => 'gray',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => __('Draft'),
            'posted' => __('Posted'),
            'voided' => __('Voided'),
            default => $this->status,
        };
    }
}
