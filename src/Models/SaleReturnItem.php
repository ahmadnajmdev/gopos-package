<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Model;

class SaleReturnItem extends Model
{
    public static function boot()
    {
        parent::boot();

        static::created(function ($saleReturnItem) {
            // Customer returns items to us = stock increases (positive)
            InventoryMovement::create([
                'product_id' => $saleReturnItem->product_id,
                'type' => 'sale_return',
                'quantity' => (int) $saleReturnItem->return_stock,
                'sale_return_id' => $saleReturnItem->sale_return_id,
                'user_id' => auth()->id(),
                'reason' => null,
                'movement_date' => now(),
            ]);
        });

        static::updated(function ($saleReturnItem) {
            $previousReturnStock = $saleReturnItem->getOriginal('return_stock');
            $currentReturnStock = $saleReturnItem->return_stock;
            if ($currentReturnStock > $previousReturnStock) {
                // Customer returning MORE to us = stock increases (positive)
                InventoryMovement::create([
                    'product_id' => $saleReturnItem->product_id,
                    'type' => 'sale_return',
                    'quantity' => (int) ($currentReturnStock - $previousReturnStock),
                    'sale_return_id' => $saleReturnItem->sale_return_id,
                    'user_id' => auth()->id(),
                    'reason' => 'Sale return item increased',
                    'movement_date' => now(),
                ]);
            } else {
                $delta = (int) ($previousReturnStock - $currentReturnStock);
                if ($delta !== 0) {
                    // Customer returning LESS to us = stock decreases (negative)
                    InventoryMovement::create([
                        'product_id' => $saleReturnItem->product_id,
                        'type' => 'sale_return',
                        'quantity' => -$delta,
                        'sale_return_id' => $saleReturnItem->sale_return_id,
                        'user_id' => auth()->id(),
                        'reason' => 'Sale return item decreased',
                        'movement_date' => now(),
                    ]);
                }
            }
        });

        static::deleted(function ($saleReturnItem) {
            // Reversing the return = stock leaves again (negative)
            InventoryMovement::create([
                'product_id' => $saleReturnItem->product_id,
                'type' => 'adjustment',
                'quantity' => -(int) $saleReturnItem->return_stock,
                'sale_return_id' => $saleReturnItem->sale_return_id,
                'user_id' => auth()->id(),
                'reason' => 'Sale Return Item Deleted',
                'movement_date' => now(),
            ]);
        });

    }

    public function saleReturn()
    {
        return $this->belongsTo(SaleReturn::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getTotalAmountAttribute()
    {
        return $this->price * $this->return_stock;
    }
}
