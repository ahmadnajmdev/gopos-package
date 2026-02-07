<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockCountItem extends Model
{
    protected $fillable = [
        'stock_count_id',
        'product_id',
        'location_id',
        'batch_id',
        'system_quantity',
        'counted_quantity',
        'variance',
        'unit_cost',
        'variance_value',
        'status',
        'counted_by',
        'verified_by',
        'notes',
    ];

    protected $casts = [
        'system_quantity' => 'decimal:4',
        'counted_quantity' => 'decimal:4',
        'variance' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'variance_value' => 'decimal:4',
    ];

    public const STATUS_PENDING = 'pending';

    public const STATUS_COUNTED = 'counted';

    public const STATUS_VERIFIED = 'verified';

    public const STATUS_ADJUSTED = 'adjusted';

    public function stockCount(): BelongsTo
    {
        return $this->belongsTo(StockCount::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class, 'location_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class, 'batch_id');
    }

    public function counter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counted_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function recordCount(float $quantity, User $user): void
    {
        $variance = $quantity - $this->system_quantity;
        $this->update([
            'counted_quantity' => $quantity,
            'variance' => $variance,
            'variance_value' => $variance * $this->unit_cost,
            'status' => self::STATUS_COUNTED,
            'counted_by' => $user->id,
        ]);
    }

    public function verify(User $user): void
    {
        $this->update([
            'status' => self::STATUS_VERIFIED,
            'verified_by' => $user->id,
        ]);
    }

    public function hasVariance(): bool
    {
        return $this->variance != 0;
    }

    public function isPositiveVariance(): bool
    {
        return $this->variance > 0;
    }

    public function isNegativeVariance(): bool
    {
        return $this->variance < 0;
    }
}
