<?php

namespace Gopos\Models;

use Gopos\Models\Concerns\BelongsToBranch;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Storage;

class Product extends Model
{
    use BelongsToBranch;
    use HasFactory;

    protected $casts = [
        'track_batches' => 'boolean',
        'has_expiry' => 'boolean',
        'track_serials' => 'boolean',
        'expiry_warning_days' => 'integer',
        'warranty_months' => 'integer',
        'average_cost' => 'decimal:4',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function attributes()
    {
        return $this->hasMany(ProductAttribute::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class, 'product_warehouses')
            ->withPivot(['quantity', 'reserved_quantity', 'minimum_stock', 'maximum_stock', 'reorder_point', 'reorder_quantity', 'location_id'])
            ->withTimestamps();
    }

    public function batches(): HasMany
    {
        return $this->hasMany(ProductBatch::class);
    }

    public function serials(): HasMany
    {
        return $this->hasMany(ProductSerial::class);
    }

    public function reorderRules(): HasMany
    {
        return $this->hasMany(ReorderRule::class);
    }

    public function getImageUrlAttribute($value)
    {
        return $value ? Storage::url($value) : 'https://placehold.co/400';
    }

    public function getStockAttribute(): int
    {
        // Calculate stock from movements only
        return (int) $this->movements()->sum('quantity');
    }

    /**
     * Get stock for a specific warehouse.
     */
    public function getWarehouseStock(?Warehouse $warehouse = null): float
    {
        if (! $warehouse) {
            return $this->stock;
        }

        $pivot = $this->warehouses()->where('warehouse_id', $warehouse->id)->first()?->pivot;

        return $pivot ? $pivot->quantity : 0;
    }

    /**
     * Get available stock (total - reserved) for a warehouse.
     */
    public function getAvailableStock(?Warehouse $warehouse = null): float
    {
        if (! $warehouse) {
            return $this->stock;
        }

        $pivot = $this->warehouses()->where('warehouse_id', $warehouse->id)->first()?->pivot;
        if (! $pivot) {
            return 0;
        }

        return $pivot->quantity - $pivot->reserved_quantity;
    }

    /**
     * Get available batches for this product (FIFO order).
     */
    public function getAvailableBatches(?Warehouse $warehouse = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->batches()->active()->withStock();

        if ($warehouse) {
            $query->where('warehouse_id', $warehouse->id);
        }

        return $query->fifo()->get();
    }

    /**
     * Get available serials for this product.
     */
    public function getAvailableSerials(?Warehouse $warehouse = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->serials()->available();

        if ($warehouse) {
            $query->where('warehouse_id', $warehouse->id);
        }

        return $query->get();
    }

    /**
     * Check if product needs batch tracking.
     */
    public function requiresBatchTracking(): bool
    {
        return $this->track_batches || $this->has_expiry;
    }

    /**
     * Check if product needs serial tracking.
     */
    public function requiresSerialTracking(): bool
    {
        return $this->track_serials;
    }

    /**
     * Scope a query to only include products that are low in stock.
     */
    public function scopeLowStock(Builder $query)
    {
        return $query
            ->select('products.*')
            ->selectRaw('(SELECT COALESCE(SUM(quantity), 0) FROM inventory_movements WHERE inventory_movements.product_id = products.id) as total_quantity')
            ->whereRaw('(SELECT COALESCE(SUM(quantity), 0) FROM inventory_movements WHERE inventory_movements.product_id = products.id) <= products.low_stock_alert');
    }

    /**
     * Scope for products with batch tracking.
     */
    public function scopeWithBatchTracking(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('track_batches', true)->orWhere('has_expiry', true);
        });
    }

    /**
     * Scope for products with serial tracking.
     */
    public function scopeWithSerialTracking(Builder $query): Builder
    {
        return $query->where('track_serials', true);
    }
}
