<?php

namespace Gopos\Models;

use Gopos\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    use Auditable;
    use HasFactory;

    protected $fillable = [
        'purchase_number',
        'purchase_date',
        'supplier_id',
        'warehouse_id',
        'currency_id',
        'exchange_rate',
        'tax_code_id',
        'tax_rate',
        'tax_amount',
        'tax_amount_in_base_currency',
        'sub_total',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'amount_in_base_currency',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'sub_total' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'amount_in_base_currency' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'tax_rate' => 'decimal:4',
        'tax_amount' => 'decimal:2',
        'tax_amount_in_base_currency' => 'decimal:2',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($purchase) {
            if (empty($purchase->purchase_number)) {
                $purchase->purchase_number = Purchase::generatePurchaseNumber();
            }

            if ($purchase->exchange_rate === null && $purchase->currency) {
                $purchase->exchange_rate = $purchase->currency->exchange_rate;
            }

            // Set amount_in_base_currency
            if ($purchase->total_amount !== null && $purchase->currency) {
                $purchase->amount_in_base_currency = $purchase->currency->convertFromCurrency($purchase->total_amount, $purchase->currency->code);
            }
        });

        // Stock movements are handled by PurchaseItem events

        // Updates are handled by PurchaseItem events
        // Deletes handled by PurchaseItem events
    }

    public function getAmountDueAttribute(): float
    {
        return $this->total_amount - $this->paid_amount;
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function taxCode(): BelongsTo
    {
        return $this->belongsTo(TaxCode::class);
    }

    public function getAmountInBaseCurrencyAttribute()
    {
        return $this->currency?->convertFromCurrency($this->total_amount, $this->currency->code);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public static function generatePurchaseNumber(): string
    {
        $lastPurchase = Purchase::latest()->first();

        return $lastPurchase ? 'PUR-'.str_pad($lastPurchase->id + 1, 5, '0', STR_PAD_LEFT) : 'PUR-00001';
    }
}
