<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    public static function boot()
    {
        parent::boot();

        static::created(function ($saleItem) {
            InventoryMovement::create([
                'product_id' => $saleItem->product_id,
                'type' => 'sale',
                'quantity' => -(int) $saleItem->stock,
                'sale_id' => $saleItem->sale_id,
                'user_id' => auth()->id(),
                'reason' => null,
                'movement_date' => now(),
            ]);
        });

        static::updated(function ($saleItem) {
            $previousStock = $saleItem->getOriginal('stock');
            $currentStock = $saleItem->stock;
            if ($currentStock > $previousStock) {
                InventoryMovement::create([
                    'product_id' => $saleItem->product_id,
                    'type' => 'sale',
                    'quantity' => -(int) ($currentStock - $previousStock),
                    'sale_id' => $saleItem->sale_id,
                    'user_id' => auth()->id(),
                    'reason' => 'Sale item increased',
                    'movement_date' => now(),
                ]);
            } else {
                $delta = (int) ($previousStock - $currentStock);
                if ($delta !== 0) {
                    InventoryMovement::create([
                        'product_id' => $saleItem->product_id,
                        'type' => 'sale',
                        'quantity' => $delta,
                        'sale_id' => $saleItem->sale_id,
                        'user_id' => auth()->id(),
                        'reason' => 'Sale item decreased',
                        'movement_date' => now(),
                    ]);
                }
            }
        });

        // detach the product from the sale if the sale item is deleted
        static::deleted(function ($saleItem) {
            InventoryMovement::create([
                'product_id' => $saleItem->product_id,
                'type' => 'adjustment',
                'quantity' => (int) $saleItem->stock,
                'sale_id' => $saleItem->sale_id,
                'user_id' => auth()->id(),
                'reason' => 'Sale item deleted',
                'movement_date' => now(),
            ]);
        });
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
