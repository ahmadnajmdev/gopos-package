<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReorderRule extends Model
{
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'supplier_id',
        'minimum_quantity',
        'maximum_quantity',
        'reorder_point',
        'reorder_quantity',
        'lead_time_days',
        'is_active',
        'auto_create_po',
    ];

    protected $casts = [
        'minimum_quantity' => 'decimal:4',
        'maximum_quantity' => 'decimal:4',
        'reorder_point' => 'decimal:4',
        'reorder_quantity' => 'decimal:4',
        'lead_time_days' => 'integer',
        'is_active' => 'boolean',
        'auto_create_po' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function needsReorder(): bool
    {
        $currentStock = $this->getCurrentStock();

        return $currentStock <= $this->reorder_point;
    }

    public function getCurrentStock(): float
    {
        if ($this->warehouse_id) {
            $pivot = ProductWarehouse::where('product_id', $this->product_id)
                ->where('warehouse_id', $this->warehouse_id)
                ->first();

            return $pivot ? $pivot->quantity : 0;
        }

        return $this->product->stock ?? 0;
    }

    public function getSuggestedOrderQuantity(): float
    {
        $currentStock = $this->getCurrentStock();

        if ($this->maximum_quantity) {
            return max(0, $this->maximum_quantity - $currentStock);
        }

        return $this->reorder_quantity;
    }

    public function getExpectedDeliveryDate(): ?\Carbon\Carbon
    {
        return now()->addDays($this->lead_time_days);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeNeedsReorder($query)
    {
        return $query->active()->get()->filter(fn ($rule) => $rule->needsReorder());
    }
}
