<?php

namespace Gopos\Listeners;

use Gopos\Events\StockCountPosted;
use Gopos\Services\GeneralLedgerService;
use Gopos\Services\InventoryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class PostStockCountAdjustments implements ShouldQueue
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected GeneralLedgerService $glService
    ) {}

    public function handle(StockCountPosted $event): void
    {
        $count = $event->stockCount;
        $totalAdjustmentValue = 0;

        foreach ($count->items as $item) {
            $variance = $item->counted_quantity - $item->system_quantity;

            if ($variance != 0) {
                $adjustmentValue = $variance * ($item->product->cost ?? 0);
                $totalAdjustmentValue += $adjustmentValue;

                // Create inventory movement
                $this->inventoryService->adjustStock(
                    productId: $item->product_id,
                    quantity: $variance,
                    referenceType: 'stock_count',
                    referenceId: $count->id,
                    warehouseId: $count->warehouse_id,
                    notes: "Stock count #{$count->count_number} adjustment"
                );
            }
        }

        // Post to GL if there's an adjustment
        if ($totalAdjustmentValue != 0) {
            $this->postAdjustmentToGL($count, $totalAdjustmentValue);
        }

        Log::info('Stock count adjustments posted', [
            'count_id' => $count->id,
            'count_number' => $count->count_number,
            'total_adjustment_value' => $totalAdjustmentValue,
        ]);
    }

    protected function postAdjustmentToGL($count, float $totalAdjustmentValue): void
    {
        $inventoryAccount = $this->glService->getAccountByCode('1300');
        $adjustmentAccount = $this->glService->getAccountByCode('5099');

        if (! $inventoryAccount || ! $adjustmentAccount) {
            Log::warning('GL accounts not found for stock count adjustment', [
                'count_id' => $count->id,
            ]);

            return;
        }

        $entries = [];

        if ($totalAdjustmentValue > 0) {
            // Inventory increase (gain)
            $entries[] = [
                'account_id' => $inventoryAccount->id,
                'debit' => $totalAdjustmentValue,
                'credit' => 0,
                'description' => "Inventory increase from count #{$count->count_number}",
            ];
            $entries[] = [
                'account_id' => $adjustmentAccount->id,
                'debit' => 0,
                'credit' => $totalAdjustmentValue,
                'description' => 'Adjustment gain',
            ];
        } else {
            // Inventory decrease (loss)
            $entries[] = [
                'account_id' => $adjustmentAccount->id,
                'debit' => abs($totalAdjustmentValue),
                'credit' => 0,
                'description' => 'Adjustment loss',
            ];
            $entries[] = [
                'account_id' => $inventoryAccount->id,
                'debit' => 0,
                'credit' => abs($totalAdjustmentValue),
                'description' => "Inventory decrease from count #{$count->count_number}",
            ];
        }

        $this->glService->createJournalEntry(
            date: now(),
            description: "Stock Count Adjustment #{$count->count_number}",
            lines: $entries,
            referenceType: 'stock_count',
            referenceId: $count->id
        );
    }
}
