<?php

namespace Gopos\Models;

use Gopos\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseReturn extends Model
{
    use BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'purchase_id',
        'currency_id',
        'exchange_rate',
        'amount_in_base_currency',
        'purchase_return_number',
        'purchase_return_date',
        'sub_total',
        'discount',
        'total_amount',
        'paid_amount',
        'reason',
        'note',
    ];

    protected $casts = [
        'purchase_return_date' => 'date',
        'sub_total' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:12',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function (PurchaseReturn $return) {
            $return->purchase_return_number = self::generatePurchaseReturnNumber();
            if ($return->exchange_rate === null && $return->currency) {
                $return->exchange_rate = $return->currency->exchange_rate;
            }

            // Set amount_in_base_currency
            if ($return->total_amount !== null && $return->currency) {
                $return->amount_in_base_currency = $return->currency->convertFromCurrency($return->total_amount, $return->currency->code);
            }
        });

        static::updating(function (PurchaseReturn $return) {
            if ($return->isDirty(['total_amount', 'currency_id', 'exchange_rate']) && $return->currency) {
                $return->amount_in_base_currency = $return->currency->convertFromCurrency($return->total_amount, $return->currency->code);
            }
        });
    }

    public static function generatePurchaseReturnNumber(): string
    {
        $branchCode = filament()->getTenant()?->code ?? 'MAIN';
        $prefix = $branchCode.'-PR-';
        $prefixLength = strlen($prefix);

        $lastNumber = static::query()
            ->where('purchase_return_number', 'like', $prefix.'%')
            ->selectRaw('MAX(CAST(SUBSTRING(purchase_return_number, ?) AS UNSIGNED)) as max_num', [$prefixLength + 1])
            ->value('max_num');

        $nextNumber = ($lastNumber ?? 0) + 1;

        return $prefix.str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    public function getAmountInBaseCurrencyAttribute()
    {
        return $this->currency?->convertFromCurrency($this->total_amount, $this->currency?->code);
    }
}
