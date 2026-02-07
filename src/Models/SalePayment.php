<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalePayment extends Model
{
    protected $fillable = [
        'sale_id',
        'pos_session_id',
        'payment_method',
        'amount',
        'currency_id',
        'exchange_rate',
        'amount_in_base_currency',
        'reference_number',
        'tendered_amount',
        'change_amount',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'amount_in_base_currency' => 'decimal:2',
        'tendered_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];

    /**
     * Get the sale this payment belongs to.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the POS session this payment belongs to.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(PosSession::class, 'pos_session_id');
    }

    /**
     * Get the currency for this payment.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Check if this is a cash payment.
     */
    public function isCash(): bool
    {
        return $this->payment_method === 'cash';
    }

    /**
     * Check if this is a card payment.
     */
    public function isCard(): bool
    {
        return $this->payment_method === 'card';
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

    /**
     * Get available payment methods.
     */
    public static function getPaymentMethods(): array
    {
        return [
            'cash' => __('Cash'),
            'card' => __('Card'),
            'bank_transfer' => __('Bank Transfer'),
            'mobile_payment' => __('Mobile Payment'),
            'credit' => __('Credit'),
        ];
    }
}
