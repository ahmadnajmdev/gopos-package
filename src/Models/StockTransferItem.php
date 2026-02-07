<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransferItem extends Model
{
    protected $fillable = [
        'stock_transfer_id',
        'product_id',
        'batch_id',
        'quantity_requested',
        'quantity_sent',
        'quantity_received',
        'unit_cost',
        'serial_ids',
        'notes',
    ];

    protected $casts = [
        'quantity_requested' => 'decimal:4',
        'quantity_sent' => 'decimal:4',
        'quantity_received' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'serial_ids' => 'array',
    ];

    public function stockTransfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class, 'batch_id');
    }

    public function getVarianceAttribute(): float
    {
        return $this->quantity_received - $this->quantity_requested;
    }

    public function getTotalValueAttribute(): float
    {
        return $this->quantity_sent * $this->unit_cost;
    }

    public function isFullyReceived(): bool
    {
        return $this->quantity_received >= $this->quantity_requested;
    }
}
