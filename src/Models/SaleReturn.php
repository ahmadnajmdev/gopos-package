<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleReturn extends Model
{
    protected $fillable = [
        'sale_id',
        'currency_id',
        'exchange_rate',
        'amount_in_base_currency',
        'sale_return_number',
        'sale_return_date',
        'sub_total',
        'discount',
        'total_amount',
        'paid_amount',
        'reason',
        'note',
    ];

    protected $casts = [
        'sale_return_date' => 'date',
        'sub_total' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function (SaleReturn $return) {
            $return->sale_return_number = self::generateSaleReturnNumber();
            if (empty($return->exchange_rate) && $return->currency) {
                $return->exchange_rate = $return->currency->exchange_rate;
            }
            if (! empty($return->total_amount) && $return->currency) {
                $return->amount_in_base_currency = $return->currency->convertFromCurrency($return->total_amount, $return->currency->code);
            }
        });

        static::updating(function (SaleReturn $return) {
            if ($return->isDirty(['total_amount', 'currency_id', 'exchange_rate']) && $return->currency) {
                $return->amount_in_base_currency = $return->currency->convertFromCurrency($return->total_amount, $return->currency->code);
            }
        });
    }

    public static function generateSaleReturnNumber(): string
    {
        $last = self::latest()->first();

        return $last ? 'SR-'.str_pad($last->id + 1, 5, '0', STR_PAD_LEFT) : 'SR-00001';
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function getAmountInBaseCurrencyAttribute()
    {
        return $this->currency->convertFromCurrency($this->total_amount, $this->currency->code);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleReturnItem::class);
    }
}
