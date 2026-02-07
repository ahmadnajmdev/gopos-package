<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FiscalPeriod extends Model
{
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'status',
        'closed_at',
        'closed_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'closed_at' => 'datetime',
    ];

    // Relationships

    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    // Scopes

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeCurrent($query)
    {
        $today = now()->toDateString();

        return $query->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today);
    }

    public function scopeForDate($query, string $date)
    {
        return $query->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date);
    }

    // Methods

    /**
     * Check if the period is open
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Check if the period is closed
     */
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    /**
     * Check if a date falls within this period
     */
    public function containsDate($date): bool
    {
        $date = \Carbon\Carbon::parse($date)->startOfDay();

        return $date->gte($this->start_date) && $date->lte($this->end_date);
    }

    /**
     * Close the fiscal period
     */
    public function close(?int $userId = null): bool
    {
        if ($this->isClosed()) {
            return false;
        }

        $this->update([
            'status' => 'closed',
            'closed_at' => now(),
            'closed_by' => $userId ?? auth()->id(),
        ]);

        return true;
    }

    /**
     * Reopen the fiscal period (for admin use)
     */
    public function reopen(): bool
    {
        if ($this->isOpen()) {
            return false;
        }

        $this->update([
            'status' => 'open',
            'closed_at' => null,
            'closed_by' => null,
        ]);

        return true;
    }

    /**
     * Get the number of days in this period
     */
    public function getDaysAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * Check if entries can be posted to this period
     */
    public function canPostEntries(): bool
    {
        return $this->isOpen();
    }
}
