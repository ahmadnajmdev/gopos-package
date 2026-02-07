<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TaxExemption extends Model
{
    protected $fillable = [
        'tax_code_id',
        'exemptable_type',
        'exemptable_id',
        'reason',
        'valid_from',
        'valid_until',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_until' => 'date',
    ];

    /**
     * Get the exemptable entity (Customer, Product, Category, etc.)
     */
    public function exemptable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the tax code for this exemption
     */
    public function taxCode(): BelongsTo
    {
        return $this->belongsTo(TaxCode::class);
    }

    /**
     * Check if the exemption is currently valid
     */
    public function isValid(): bool
    {
        $today = now()->startOfDay();

        if ($today->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && $today->gt($this->valid_until)) {
            return false;
        }

        return true;
    }

    /**
     * Check if exemption covers a specific date
     */
    public function coversDate($date): bool
    {
        $date = \Carbon\Carbon::parse($date)->startOfDay();

        if ($date->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && $date->gt($this->valid_until)) {
            return false;
        }

        return true;
    }

    /**
     * Scope for currently valid exemptions
     */
    public function scopeValid($query)
    {
        $today = now()->toDateString();

        return $query->where('valid_from', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', $today);
            });
    }

    /**
     * Scope for exemptions of a specific entity
     */
    public function scopeForEntity($query, Model $entity)
    {
        return $query->where('exemptable_type', get_class($entity))
            ->where('exemptable_id', $entity->id);
    }
}
