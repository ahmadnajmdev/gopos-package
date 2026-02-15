<?php

namespace Gopos\Models;

use Gopos\Enums\QuotationStatus;
use Gopos\Models\Concerns\Auditable;
use Gopos\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    use Auditable;
    use BelongsToBranch;
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'customer_id',
        'currency_id',
        'exchange_rate',
        'amount_in_base_currency',
        'quotation_number',
        'quotation_date',
        'valid_until',
        'status',
        'tax_code_id',
        'tax_rate',
        'tax_amount',
        'tax_amount_in_base_currency',
        'sub_total',
        'discount',
        'total_amount',
        'notes',
        'converted_sale_id',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'valid_until' => 'date',
        'status' => QuotationStatus::class,
        'exchange_rate' => 'decimal:12',
        'amount_in_base_currency' => 'decimal:2',
        'tax_rate' => 'decimal:4',
        'tax_amount' => 'decimal:2',
        'tax_amount_in_base_currency' => 'decimal:2',
        'sub_total' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($quotation) {
            if (empty($quotation->quotation_number)) {
                $quotation->quotation_number = self::generateQuotationNumber();
            }

            if ($quotation->exchange_rate === null && $quotation->currency) {
                $quotation->exchange_rate = $quotation->currency->exchange_rate;
            }

            if ($quotation->total_amount !== null && $quotation->currency) {
                $quotation->amount_in_base_currency = $quotation->currency->convertFromCurrency($quotation->total_amount, $quotation->currency->code);
            }
        });

        static::updating(function ($quotation) {
            if ($quotation->isDirty(['total_amount', 'currency_id', 'exchange_rate']) && $quotation->currency) {
                $quotation->amount_in_base_currency = $quotation->currency->convertFromCurrency($quotation->total_amount, $quotation->currency->code);
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function taxCode(): BelongsTo
    {
        return $this->belongsTo(TaxCode::class);
    }

    public function convertedSale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'converted_sale_id');
    }

    public function isExpired(): bool
    {
        return $this->valid_until && $this->valid_until->isPast();
    }

    public function isConvertible(): bool
    {
        return in_array($this->status, [QuotationStatus::Draft, QuotationStatus::Sent])
            && ! $this->isExpired();
    }

    public static function generateQuotationNumber(): string
    {
        $branchCode = filament()->getTenant()?->code ?? 'MAIN';
        $prefix = $branchCode.'-QUO-';
        $prefixLength = strlen($prefix);

        $lastNumber = static::query()
            ->where('quotation_number', 'like', $prefix.'%')
            ->selectRaw('MAX(CAST(SUBSTRING(quotation_number, ?) AS UNSIGNED)) as max_num', [$prefixLength + 1])
            ->value('max_num');

        $nextNumber = ($lastNumber ?? 0) + 1;

        return $prefix.str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }
}
