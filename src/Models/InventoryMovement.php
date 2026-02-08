<?php

namespace Gopos\Models;

use Gopos\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    use BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'product_id',
        'warehouse_id',
        'location_id',
        'batch_id',
        'serial_ids',
        'type',
        'quantity',
        'unit_cost',
        'purchase_id',
        'sale_id',
        'sale_return_id',
        'purchase_return_id',
        'user_id',
        'reason',
        'movement_date',
    ];

    protected $casts = [
        'movement_date' => 'datetime',
        'serial_ids' => 'array',
        'unit_cost' => 'decimal:4',
    ];

    // Movement types
    public const TYPE_PURCHASE = 'purchase';

    public const TYPE_SALE = 'sale';

    public const TYPE_ADJUSTMENT = 'adjustment';

    public const TYPE_TRANSFER_IN = 'transfer_in';

    public const TYPE_TRANSFER_OUT = 'transfer_out';

    public const TYPE_RETURN_IN = 'return_in';

    public const TYPE_RETURN_OUT = 'return_out';

    public const TYPE_COUNT_ADJUSTMENT = 'count_adjustment';

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class, 'location_id');
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

    public function saleReturn(): BelongsTo
    {
        return $this->belongsTo(SaleReturn::class);
    }

    public function purchaseReturn(): BelongsTo
    {
        return $this->belongsTo(PurchaseReturn::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get serials associated with this movement.
     */
    public function getSerials()
    {
        if (empty($this->serial_ids)) {
            return collect();
        }

        return ProductSerial::whereIn('id', $this->serial_ids)->get();
    }

    /**
     * Get total value of movement.
     */
    public function getTotalValueAttribute(): float
    {
        return abs($this->quantity) * ($this->unit_cost ?? 0);
    }

    /**
     * Check if this is an incoming movement (adds stock).
     */
    public function isIncoming(): bool
    {
        return $this->quantity > 0;
    }

    /**
     * Check if this is an outgoing movement (removes stock).
     */
    public function isOutgoing(): bool
    {
        return $this->quantity < 0;
    }

    /**
     * Scope for movements by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for movements in a warehouse.
     */
    public function scopeInWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Scope for movements in date range.
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('movement_date', [$startDate, $endDate]);
    }
}
