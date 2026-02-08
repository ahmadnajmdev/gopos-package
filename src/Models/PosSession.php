<?php

namespace Gopos\Models;

use Gopos\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosSession extends Model
{
    use BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'user_id',
        'terminal_id',
        'opening_time',
        'closing_time',
        'opening_cash',
        'closing_cash',
        'expected_cash',
        'cash_difference',
        'status',
        'notes',
        'closed_by',
    ];

    protected $casts = [
        'opening_time' => 'datetime',
        'closing_time' => 'datetime',
        'opening_cash' => 'decimal:2',
        'closing_cash' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'cash_difference' => 'decimal:2',
    ];

    /**
     * Get the user that owns this session.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who closed this session.
     */
    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /**
     * Get all transactions for this session.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(PosSessionTransaction::class);
    }

    /**
     * Get all sales for this session.
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Get all held sales for this session.
     */
    public function heldSales(): HasMany
    {
        return $this->hasMany(HeldSale::class);
    }

    /**
     * Check if session is open.
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Check if session is closed.
     */
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    /**
     * Get total cash sales for this session.
     */
    public function getTotalCashSalesAttribute(): float
    {
        return $this->transactions()
            ->where('type', 'sale')
            ->where('payment_method', 'cash')
            ->sum('amount');
    }

    /**
     * Get total cash refunds for this session.
     */
    public function getTotalCashRefundsAttribute(): float
    {
        return $this->transactions()
            ->where('type', 'refund')
            ->where('payment_method', 'cash')
            ->sum('amount');
    }

    /**
     * Get total cash in for this session.
     */
    public function getTotalCashInAttribute(): float
    {
        return $this->transactions()
            ->where('type', 'cash_in')
            ->sum('amount');
    }

    /**
     * Get total cash out for this session.
     */
    public function getTotalCashOutAttribute(): float
    {
        return $this->transactions()
            ->whereIn('type', ['cash_out', 'expense'])
            ->sum('amount');
    }

    /**
     * Calculate expected cash in drawer.
     */
    public function calculateExpectedCash(): float
    {
        return $this->opening_cash
            + $this->total_cash_sales
            - $this->total_cash_refunds
            + $this->total_cash_in
            - $this->total_cash_out;
    }

    /**
     * Get sales count for this session.
     */
    public function getSalesCountAttribute(): int
    {
        return $this->sales()->count();
    }

    /**
     * Get total sales amount for this session.
     */
    public function getTotalSalesAmountAttribute(): float
    {
        return $this->sales()->sum('total_amount');
    }

    /**
     * Scope for open sessions.
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope for closed sessions.
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    /**
     * Scope for sessions by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for today's sessions.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('opening_time', today());
    }
}
