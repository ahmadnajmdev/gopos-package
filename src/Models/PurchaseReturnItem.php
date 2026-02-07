<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseReturnItem extends Model
{
    protected $fillable = [
        'purchase_return_id',
        'product_id',
        'price',
        'return_stock',
        'return_discount_amount',
        'return_total_amount',
        'note',
    ];

    public static function boot()
    {
        parent::boot();

        static::created(function ($purchaseReturnItem) {
            // Returning items to supplier = stock decreases (negative)
            InventoryMovement::create([
                'product_id' => $purchaseReturnItem->product_id,
                'type' => 'purchase_return',
                'quantity' => -(int) $purchaseReturnItem->return_stock,
                'purchase_return_id' => $purchaseReturnItem->purchase_return_id,
                'user_id' => auth()->id(),
                'reason' => null,
                'movement_date' => now(),
            ]);
        });

        static::updated(function ($purchaseReturnItem) {
            $previousReturnStock = $purchaseReturnItem->getOriginal('return_stock');
            $currentReturnStock = $purchaseReturnItem->return_stock;
            if ($currentReturnStock > $previousReturnStock) {
                // Returning MORE to supplier = stock decreases (negative)
                InventoryMovement::create([
                    'product_id' => $purchaseReturnItem->product_id,
                    'type' => 'purchase_return',
                    'quantity' => -(int) ($currentReturnStock - $previousReturnStock),
                    'purchase_return_id' => $purchaseReturnItem->purchase_return_id,
                    'user_id' => auth()->id(),
                    'reason' => 'Purchase return item increased',
                    'movement_date' => now(),
                ]);
            } else {
                $delta = (int) ($previousReturnStock - $currentReturnStock);
                if ($delta !== 0) {
                    // Returning LESS to supplier = stock increases (positive)
                    InventoryMovement::create([
                        'product_id' => $purchaseReturnItem->product_id,
                        'type' => 'purchase_return',
                        'quantity' => $delta,
                        'purchase_return_id' => $purchaseReturnItem->purchase_return_id,
                        'user_id' => auth()->id(),
                        'reason' => 'Purchase return item decreased',
                        'movement_date' => now(),
                    ]);
                }
            }
        });

        static::deleted(function ($purchaseReturnItem) {
            // Reversing the return = stock comes back (positive)
            InventoryMovement::create([
                'product_id' => $purchaseReturnItem->product_id,
                'type' => 'adjustment',
                'quantity' => (int) $purchaseReturnItem->return_stock,
                'purchase_return_id' => $purchaseReturnItem->purchase_return_id,
                'user_id' => auth()->id(),
                'reason' => 'Purchase Return Item Deleted',
                'movement_date' => now(),
            ]);
        });
    }

    public function purchaseReturn(): BelongsTo
    {
        return $this->belongsTo(PurchaseReturn::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
