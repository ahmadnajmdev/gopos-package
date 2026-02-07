<?php

namespace Gopos\Listeners;

use Gopos\Events\SaleCreated;
use Gopos\Services\InventoryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class UpdateInventoryOnSale implements ShouldQueue
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    public function handle(SaleCreated $event): void
    {
        $sale = $event->sale;

        foreach ($sale->items as $item) {
            $this->inventoryService->reduceStock(
                productId: $item->product_id,
                quantity: $item->quantity,
                referenceType: 'sale',
                referenceId: $sale->id,
                warehouseId: $sale->warehouse_id ?? null,
                notes: "Sale #{$sale->sale_number}"
            );
        }

        Log::info('Inventory updated for sale', [
            'sale_id' => $sale->id,
            'sale_number' => $sale->sale_number,
            'items_count' => $sale->items->count(),
        ]);
    }
}
