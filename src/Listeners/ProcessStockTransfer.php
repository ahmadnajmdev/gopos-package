<?php

namespace Gopos\Listeners;

use Gopos\Events\StockTransferCompleted;
use Gopos\Services\InventoryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class ProcessStockTransfer implements ShouldQueue
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    public function handle(StockTransferCompleted $event): void
    {
        $transfer = $event->stockTransfer;

        foreach ($transfer->items as $item) {
            // Reduce from source warehouse
            $this->inventoryService->reduceStock(
                productId: $item->product_id,
                quantity: $item->quantity_sent,
                referenceType: 'transfer_out',
                referenceId: $transfer->id,
                warehouseId: $transfer->from_warehouse_id,
                notes: "Transfer #{$transfer->transfer_number} to {$transfer->toWarehouse->name}"
            );

            // Add to destination warehouse
            $this->inventoryService->addStock(
                productId: $item->product_id,
                quantity: $item->quantity_received,
                referenceType: 'transfer_in',
                referenceId: $transfer->id,
                warehouseId: $transfer->to_warehouse_id,
                notes: "Transfer #{$transfer->transfer_number} from {$transfer->fromWarehouse->name}"
            );
        }

        Log::info('Stock transfer processed', [
            'transfer_id' => $transfer->id,
            'transfer_number' => $transfer->transfer_number,
            'from_warehouse' => $transfer->from_warehouse_id,
            'to_warehouse' => $transfer->to_warehouse_id,
        ]);
    }
}
