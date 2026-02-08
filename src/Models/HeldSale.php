<?php

namespace Gopos\Models;

use Gopos\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HeldSale extends Model
{
    use BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'user_id',
        'pos_session_id',
        'customer_id',
        'hold_reference',
        'cart_data',
        'form_data',
        'notes',
        'expires_at',
    ];

    protected $casts = [
        'cart_data' => 'array',
        'form_data' => 'array',
        'expires_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->hold_reference)) {
                $model->hold_reference = self::generateReference();
            }
        });
    }

    /**
     * Generate a unique hold reference.
     */
    public static function generateReference(): string
    {
        $prefix = 'HOLD';
        $number = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        return $prefix.'-'.now()->format('Ymd').'-'.$number;
    }

    /**
     * Get the user that created this hold.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the POS session this hold belongs to.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(PosSession::class, 'pos_session_id');
    }

    /**
     * Get the customer for this held sale.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Check if this hold has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Get the total amount from cart data.
     */
    public function getTotalAmountAttribute(): float
    {
        return $this->form_data['total_amount'] ?? 0;
    }

    /**
     * Get the items count from cart data.
     */
    public function getItemsCountAttribute(): int
    {
        return count($this->cart_data ?? []);
    }

    /**
     * Scope for non-expired holds.
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope for holds by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for holds by session.
     */
    public function scopeBySession($query, $sessionId)
    {
        return $query->where('pos_session_id', $sessionId);
    }
}
