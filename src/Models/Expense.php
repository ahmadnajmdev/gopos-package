<?php

namespace Gopos\Models;

use Gopos\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use Auditable;

    protected $fillable = [
        'expense_date',
        'expense_type_id',
        'cost_center_id',
        'currency_id',
        'exchange_rate',
        'amount_in_base_currency',
        'amount',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($expense) {
            // Set exchange_rate from currency if not set
            if (empty($expense->exchange_rate) && $expense->currency) {
                $expense->exchange_rate = $expense->currency->exchange_rate;
            }

            // Set amount_in_base_currency
            if (! empty($expense->amount) && $expense->currency) {
                $expense->amount_in_base_currency = $expense->currency->convertFromCurrency($expense->amount, $expense->currency->code);
            }
        });
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function getAmountInBaseCurrencyAttribute()
    {
        return $this->currency?->convertFromCurrency($this->amount, $this->currency->code);
    }

    public function type()
    {
        return $this->belongsTo(ExpenseType::class, 'expense_type_id');
    }
}
