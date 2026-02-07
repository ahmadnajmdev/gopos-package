<?php

namespace Gopos\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSerial extends Model
{
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'batch_id',
        'serial_number',
        'status',
        'cost',
        'purchase_id',
        'purchase_item_id',
        'sale_id',
        'sale_item_id',
        'warranty_start',
        'warranty_end',
        'notes',
    ];

    protected $casts = [
        'cost' => 'decimal:4',
        'warranty_start' => 'date',
        'warranty_end' => 'date',
    ];

    public const STATUS_AVAILABLE = 'available';

    public const STATUS_SOLD = 'sold';

    public const STATUS_RESERVED = 'reserved';

    public const STATUS_DAMAGED = 'damaged';

    public const STATUS_RETURNED = 'returned';

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class, 'batch_id');
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    public function hasActiveWarranty(): bool
    {
        return $this->warranty_end && $this->warranty_end->isFuture();
    }

    public function markAsSold(Sale $sale, SaleItem $saleItem): void
    {
        $this->update([
            'status' => self::STATUS_SOLD,
            'sale_id' => $sale->id,
            'sale_item_id' => $saleItem->id,
        ]);
    }

    public function markAsAvailable(): void
    {
        $this->update([
            'status' => self::STATUS_AVAILABLE,
            'sale_id' => null,
            'sale_item_id' => null,
        ]);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    public function scopeSold(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SOLD);
    }
}
