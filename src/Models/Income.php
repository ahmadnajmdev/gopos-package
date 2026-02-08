<?php

namespace Gopos\Models;

use Gopos\Models\Concerns\Auditable;
use Gopos\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Income extends Model
{
    use Auditable;
    use BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'income_date',
        'income_type_id',
        'cost_center_id',
        'currency_id',
        'exchange_rate',
        'amount_in_base_currency',
        'amount',
        'description',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'income_date' => 'date',
            'exchange_rate' => 'decimal:12',
        ];
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($income) {

            // Set exchange_rate from currency if not set
            if (empty($income->exchange_rate) && $income->currency) {
                $income->exchange_rate = $income->currency->exchange_rate;
            }

            // Set amount_in_base_currency
            if (! empty($income->amount) && $income->currency) {
                $income->amount_in_base_currency = $income->currency->convertFromCurrency($income->amount, $income->currency->code);
            }
        });

        static::updating(function ($income) {
            // Update amount_in_base_currency when amount or currency changes
            if ($income->isDirty(['amount', 'currency_id', 'exchange_rate']) && $income->currency) {
                $income->amount_in_base_currency = $income->currency->convertFromCurrency($income->amount, $income->currency->code);
            }
        });
    }

    public function type()
    {
        return $this->belongsTo(IncomeType::class, 'income_type_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
