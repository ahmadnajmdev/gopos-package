<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'symbol',
        'code',
        'exchange_rate',
        'decimal_places',
        'base',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function incomes()
    {
        return $this->hasMany(Income::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public static function convertToCurrency($amount, $from, $to)
    {
        $fromCurrency = self::where('code', $from)->first();
        $toCurrency = self::where('code', $to)->first();

        return $amount * $fromCurrency->exchange_rate / $toCurrency->exchange_rate;
    }

    /**
     * Convert from currency to base currency
     */
    /**
     * Convert an amount from a given currency to the base currency.
     */
    public function convertFromCurrency(float|int|null $amount, string|int|self $from): float
    {
        // If amount is null, treat as 0.0
        if ($amount === null) {
            return 0.0;
        }

        $baseCurrency = self::getBaseCurrency();

        if (is_numeric($from)) {
            $fromCurrency = self::find($from);
        } elseif ($from instanceof self) {
            $fromCurrency = $from;
        } else {
            $fromCurrency = self::where('code', $from)->first();
        }

        if (
            ! $fromCurrency ||
            ! $fromCurrency->exchange_rate ||
            ! $baseCurrency ||
            ! $baseCurrency->exchange_rate
        ) {
            return (float) $amount;
        }

        // Convert amount from $fromCurrency to base currency
        return (float) ($amount * $baseCurrency->exchange_rate / $fromCurrency->exchange_rate);
    }

    public static function getBaseCurrency(): ?Currency
    {
        return self::where('base', true)->first();
    }
}
