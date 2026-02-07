<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyTransaction extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'customer_loyalty_id',
        'type',
        'points',
        'sale_id',
        'description',
        'created_at',
    ];

    protected $casts = [
        'points' => 'integer',
        'created_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = $model->created_at ?? now();
        });
    }

    /**
     * Get the customer loyalty record.
     */
    public function customerLoyalty(): BelongsTo
    {
        return $this->belongsTo(CustomerLoyalty::class);
    }

    /**
     * Get the sale (if any).
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Check if this is an earning transaction.
     */
    public function isEarning(): bool
    {
        return in_array($this->type, ['earn', 'bonus', 'adjust']) && $this->points > 0;
    }

    /**
     * Check if this is a redemption transaction.
     */
    public function isRedemption(): bool
    {
        return $this->type === 'redeem';
    }

    /**
     * Get localized type name.
     */
    public function getLocalizedTypeAttribute(): string
    {
        return match ($this->type) {
            'earn' => __('Earned'),
            'redeem' => __('Redeemed'),
            'expire' => __('Expired'),
            'adjust' => __('Adjustment'),
            'bonus' => __('Bonus'),
            default => $this->type,
        };
    }
}
