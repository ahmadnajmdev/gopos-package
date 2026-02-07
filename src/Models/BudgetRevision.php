<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetRevision extends Model
{
    protected $fillable = [
        'budget_id',
        'revision_number',
        'reason',
        'previous_total',
        'new_total',
        'changes',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'previous_total' => 'decimal:2',
        'new_total' => 'decimal:2',
        'changes' => 'array',
        'approved_at' => 'datetime',
    ];

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the change amount.
     */
    public function getChangeAmountAttribute(): float
    {
        return $this->new_total - $this->previous_total;
    }

    /**
     * Get the change percentage.
     */
    public function getChangePercentAttribute(): float
    {
        if ($this->previous_total == 0) {
            return 0;
        }

        return (($this->new_total - $this->previous_total) / $this->previous_total) * 100;
    }

    /**
     * Approve the revision.
     */
    public function approve(): void
    {
        $this->update([
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
    }

    /**
     * Check if approved.
     */
    public function isApproved(): bool
    {
        return ! is_null($this->approved_at);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->created_by)) {
                $model->created_by = auth()->id();
            }

            // Auto-generate revision number
            $lastRevision = static::where('budget_id', $model->budget_id)
                ->max('revision_number');
            $model->revision_number = ($lastRevision ?? 0) + 1;
        });
    }
}
