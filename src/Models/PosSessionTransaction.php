<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PosSessionTransaction extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'pos_session_id',
        'type',
        'reference_type',
        'reference_id',
        'amount',
        'payment_method',
        'currency_id',
        'exchange_rate',
        'notes',
        'created_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:12',
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
     * Get the session this transaction belongs to.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(PosSession::class, 'pos_session_id');
    }

    /**
     * Get the currency for this transaction.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Get the reference model (polymorphic).
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get amount in base currency.
     */
    public function getAmountInBaseCurrencyAttribute(): float
    {
        return $this->amount * $this->exchange_rate;
    }

    /**
     * Check if this is a cash transaction.
     */
    public function isCash(): bool
    {
        return $this->payment_method === 'cash';
    }

    /**
     * Get localized type name.
     */
    public function getLocalizedTypeAttribute(): string
    {
        return match ($this->type) {
            'sale' => __('Sale'),
            'refund' => __('Refund'),
            'cash_in' => __('Cash In'),
            'cash_out' => __('Cash Out'),
            'expense' => __('Expense'),
            default => $this->type,
        };
    }

    /**
     * Get localized payment method.
     */
    public function getLocalizedPaymentMethodAttribute(): string
    {
        return match ($this->payment_method) {
            'cash' => __('Cash'),
            'card' => __('Card'),
            'bank_transfer' => __('Bank Transfer'),
            'mobile_payment' => __('Mobile Payment'),
            'credit' => __('Credit'),
            default => $this->payment_method,
        };
    }
}
