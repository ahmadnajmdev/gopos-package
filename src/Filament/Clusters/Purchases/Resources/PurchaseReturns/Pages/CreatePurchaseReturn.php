<?php

namespace Gopos\Filament\Clusters\Purchases\Resources\PurchaseReturns\Pages;

use Filament\Resources\Pages\CreateRecord;
use Gopos\Filament\Clusters\Purchases\Resources\PurchaseReturns\PurchaseReturnResource;
use Gopos\Models\Purchase;

class CreatePurchaseReturn extends CreateRecord
{
    protected static string $resource = PurchaseReturnResource::class;

    public function mount(): void
    {
        $purchase_id = request()->query('purchase_id');
        // Get purchase data and set to form purchase return
        $purchase = Purchase::with(['currency', 'items'])->find($purchase_id);

        if ($purchase) {
            $items = [];
            foreach ($purchase->items as $item) {
                $items[] = [
                    'product_id' => $item->product_id,
                    'cost' => $item->cost,
                    'return_stock' => $item->stock,
                    'return_total_amount' => $item->total_amount,
                ];
            }

            $this->form->fill([
                'purchase_id' => $purchase_id,
                'currency_id' => $purchase->currency_id,
                'purchase_return_number' => \Gopos\Models\PurchaseReturn::generatePurchaseReturnNumber(),
                'purchase_return_date' => now(),
                'items' => $items,
                'sub_total' => $purchase->sub_total,
                'discount' => $purchase->discount,
                'total_amount' => $purchase->total_amount,
                'paid_amount' => $purchase->paid_amount,
            ]);

        } else {
            $this->form->fill([
                'purchase_id' => null,
                'currency_id' => null,
                'purchase_return_number' => \Gopos\Models\PurchaseReturn::generatePurchaseReturnNumber(),
                'purchase_return_date' => now(),
                'status' => 'Pending',
                'discount' => 0,
                'paid_amount' => 0,
                'sub_total' => 0,
                'total_amount' => 0,
                'items' => [],
            ]);
        }
    }
}
