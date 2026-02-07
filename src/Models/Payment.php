<?php

namespace Gopos\Models;

use Gopos\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use Auditable;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            // Set exchange_rate from currency if not set
            if (empty($payment->exchange_rate) && $payment->currency) {
                $payment->exchange_rate = $payment->currency->exchange_rate;
            }

            // Set amount_in_base_currency
            if (! empty($payment->amount) && $payment->currency) {
                $payment->amount_in_base_currency = $payment->currency->convertFromCurrency($payment->amount, $payment->currency->code);
            }
        });
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function getAmountInBaseCurrencyAttribute()
    {
        return $this->currency->convertFromCurrency($this->amount, $this->currency->code);
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public function getReferenceTypeAttribute()
    {
        return $this->reference_type;
    }

    public function getReferenceIdAttribute()
    {
        return $this->reference_id;
    }
}
