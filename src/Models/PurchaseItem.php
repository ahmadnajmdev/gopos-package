<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    public static function boot()
    {
        parent::boot();

        static::created(function ($purchaseItem) {
            InventoryMovement::create([
                'product_id' => $purchaseItem->product_id,
                'type' => 'purchase',
                'quantity' => (int) $purchaseItem->stock,
                'purchase_id' => $purchaseItem->purchase_id,
                'user_id' => auth()->id(),
                'reason' => null,
                'movement_date' => now(),
            ]);
        });

        static::updated(function ($purchaseItem) {
            $previousStock = $purchaseItem->getOriginal('stock');
            $currentStock = $purchaseItem->stock;
            if ($currentStock > $previousStock) {
                InventoryMovement::create([
                    'product_id' => $purchaseItem->product_id,
                    'type' => 'purchase',
                    'quantity' => (int) ($currentStock - $previousStock),
                    'purchase_id' => $purchaseItem->purchase_id,
                    'user_id' => auth()->id(),
                    'reason' => 'Purchase item increased',
                    'movement_date' => now(),
                ]);
            } else {
                $delta = (int) ($previousStock - $currentStock);
                if ($delta !== 0) {
                    InventoryMovement::create([
                        'product_id' => $purchaseItem->product_id,
                        'type' => 'purchase',
                        'quantity' => -$delta,
                        'purchase_id' => $purchaseItem->purchase_id,
                        'user_id' => auth()->id(),
                        'reason' => 'Purchase item decreased',
                        'movement_date' => now(),
                    ]);
                }
            }
        });

        // detach the product from the purchase if the purchase item is deleted
        static::deleted(function ($purchaseItem) {
            InventoryMovement::create([
                'product_id' => $purchaseItem->product_id,
                'type' => 'adjustment',
                'quantity' => -(int) $purchaseItem->stock,
                'purchase_id' => $purchaseItem->purchase_id,
                'user_id' => auth()->id(),
                'reason' => 'Purchase item deleted',
                'movement_date' => now(),
            ]);
        });
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
