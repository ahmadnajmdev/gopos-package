<?php

namespace Gopos\Listeners;

use Gopos\Events\SaleReturnCreated;
use Gopos\Services\GeneralLedgerService;
use Gopos\Services\InventoryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class ProcessSaleReturn implements ShouldQueue
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected GeneralLedgerService $glService
    ) {}

    public function handle(SaleReturnCreated $event): void
    {
        $return = $event->saleReturn;

        // Restore inventory
        foreach ($return->items as $item) {
            $this->inventoryService->addStock(
                productId: $item->product_id,
                quantity: $item->quantity,
                referenceType: 'sale_return',
                referenceId: $return->id,
                warehouseId: $return->sale->warehouse_id ?? null,
                notes: "Return #{$return->return_number}"
            );
        }

        // Post GL entries
        $this->postReturnToGL($return);

        Log::info('Sale return processed', [
            'return_id' => $return->id,
            'return_number' => $return->return_number,
        ]);
    }

    protected function postReturnToGL($return): void
    {
        $cashAccount = $this->glService->getAccountByCode('1001');
        $salesReturnAccount = $this->glService->getAccountByCode('4003');

        if (! $cashAccount || ! $salesReturnAccount) {
            return;
        }

        $entries = [
            [
                'account_id' => $salesReturnAccount->id,
                'debit' => $return->total_amount,
                'credit' => 0,
                'description' => "Sales return #{$return->return_number}",
            ],
            [
                'account_id' => $cashAccount->id,
                'debit' => 0,
                'credit' => $return->total_amount,
                'description' => "Refund for return #{$return->return_number}",
            ],
        ];

        $this->glService->createJournalEntry(
            date: $return->return_date,
            description: "Sales Return #{$return->return_number}",
            lines: $entries,
            referenceType: 'sale_return',
            referenceId: $return->id
        );

        // Reverse COGS
        $this->reverseCOGS($return);
    }

    protected function reverseCOGS($return): void
    {
        $totalCost = 0;
        foreach ($return->items as $item) {
            if ($item->product) {
                $totalCost += ($item->product->cost ?? 0) * $item->quantity;
            }
        }

        if ($totalCost <= 0) {
            return;
        }

        $inventoryAccount = $this->glService->getAccountByCode('1300');
        $cogsAccount = $this->glService->getAccountByCode('5001');

        if (! $inventoryAccount || ! $cogsAccount) {
            return;
        }

        $this->glService->createJournalEntry(
            date: $return->return_date,
            description: "COGS Reversal for Return #{$return->return_number}",
            lines: [
                [
                    'account_id' => $inventoryAccount->id,
                    'debit' => $totalCost,
                    'credit' => 0,
                    'description' => 'Inventory restored',
                ],
                [
                    'account_id' => $cogsAccount->id,
                    'debit' => 0,
                    'credit' => $totalCost,
                    'description' => 'COGS reversed',
                ],
            ],
            referenceType: 'sale_return_cogs',
            referenceId: $return->id
        );
    }
}
