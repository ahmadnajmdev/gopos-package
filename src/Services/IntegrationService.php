<?php

namespace Gopos\Services;

use Gopos\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IntegrationService
{
    public function __construct(
        protected GeneralLedgerService $glService,
        protected InventoryService $inventoryService,
        protected TaxCalculationService $taxService,
    ) {}

    /**
     * Process a sale with all integrations (inventory, accounting)
     */
    public function processSale(Sale $sale): void
    {
        DB::transaction(function () use ($sale) {
            // 1. Update inventory for each item
            $this->updateInventoryForSale($sale);

            // 2. Create GL entries for the sale
            $this->postSaleToGL($sale);

            // 3. Update customer balance if applicable
            if ($sale->customer_id && $sale->payment_status !== 'paid') {
                $this->updateCustomerBalance($sale);
            }

            Log::info('Sale processed with integrations', [
                'sale_id' => $sale->id,
                'sale_number' => $sale->sale_number,
            ]);
        });
    }

    /**
     * Update inventory when a sale is created
     */
    protected function updateInventoryForSale(Sale $sale): void
    {
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
    }

    /**
     * Post sale to General Ledger
     */
    protected function postSaleToGL(Sale $sale): void
    {
        $entries = [];

        // Debit: Cash/Accounts Receivable
        $debitAccount = $sale->payment_status === 'paid'
            ? $this->glService->getAccountByCode('1001') // Cash
            : $this->glService->getAccountByCode('1200'); // Accounts Receivable

        $entries[] = [
            'account_id' => $debitAccount->id,
            'debit' => $sale->total_amount,
            'credit' => 0,
            'description' => "Sale #{$sale->sale_number}",
        ];

        // Credit: Sales Revenue
        $salesAccount = $this->glService->getAccountByCode('4001');
        $entries[] = [
            'account_id' => $salesAccount->id,
            'debit' => 0,
            'credit' => $sale->sub_total,
            'description' => "Sales revenue - #{$sale->sale_number}",
        ];

        // Credit: Tax Payable (if applicable)
        if ($sale->tax_amount > 0) {
            $taxAccount = $this->glService->getAccountByCode('2100');
            $entries[] = [
                'account_id' => $taxAccount->id,
                'debit' => 0,
                'credit' => $sale->tax_amount,
                'description' => "Tax payable - Sale #{$sale->sale_number}",
            ];
        }

        // Credit: Discount (if applicable)
        if ($sale->discount_amount > 0) {
            $discountAccount = $this->glService->getAccountByCode('4002');
            $entries[] = [
                'account_id' => $discountAccount->id,
                'debit' => $sale->discount_amount,
                'credit' => 0,
                'description' => "Sales discount - #{$sale->sale_number}",
            ];
        }

        // Post COGS entry
        $this->postCOGSEntry($sale);

        $this->glService->createJournalEntry(
            date: $sale->sale_date,
            description: "Sale #{$sale->sale_number}",
            lines: $entries,
            referenceType: 'sale',
            referenceId: $sale->id
        );
    }

    /**
     * Post Cost of Goods Sold entry
     */
    protected function postCOGSEntry(Sale $sale): void
    {
        $totalCost = 0;

        foreach ($sale->items as $item) {
            $product = $item->product;
            $totalCost += $product->cost * $item->quantity;
        }

        if ($totalCost <= 0) {
            return;
        }

        $cogsAccount = $this->glService->getAccountByCode('5001');
        $inventoryAccount = $this->glService->getAccountByCode('1300');

        $this->glService->createJournalEntry(
            date: $sale->sale_date,
            description: "COGS for Sale #{$sale->sale_number}",
            lines: [
                [
                    'account_id' => $cogsAccount->id,
                    'debit' => $totalCost,
                    'credit' => 0,
                    'description' => 'Cost of goods sold',
                ],
                [
                    'account_id' => $inventoryAccount->id,
                    'debit' => 0,
                    'credit' => $totalCost,
                    'description' => 'Inventory reduction',
                ],
            ],
            referenceType: 'sale_cogs',
            referenceId: $sale->id
        );
    }

    /**
     * Update customer balance for credit sales
     */
    protected function updateCustomerBalance(Sale $sale): void
    {
        $customer = $sale->customer;
        if ($customer) {
            $customer->increment('balance', $sale->total_amount - $sale->paid_amount);
        }
    }

    /**
     * Process sale return with all integrations
     */
    public function processSaleReturn(\Gopos\Models\SaleReturn $return): void
    {
        DB::transaction(function () use ($return) {
            // 1. Restore inventory
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

            // 2. Reverse GL entries
            $this->reverseSaleGLEntries($return);

            Log::info('Sale return processed', [
                'return_id' => $return->id,
                'return_number' => $return->return_number,
            ]);
        });
    }

    /**
     * Reverse GL entries for sale return
     */
    protected function reverseSaleGLEntries(\Gopos\Models\SaleReturn $return): void
    {
        $cashAccount = $this->glService->getAccountByCode('1001');
        $salesReturnAccount = $this->glService->getAccountByCode('4003');
        $inventoryAccount = $this->glService->getAccountByCode('1300');
        $cogsAccount = $this->glService->getAccountByCode('5001');

        // Reverse sales entry
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
        $totalCost = 0;
        foreach ($return->items as $item) {
            $totalCost += $item->product->cost * $item->quantity;
        }

        if ($totalCost > 0) {
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

    /**
     * Process stock transfer completion
     */
    public function processStockTransfer(\Gopos\Models\StockTransfer $transfer): void
    {
        DB::transaction(function () use ($transfer) {
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

            Log::info('Stock transfer completed', [
                'transfer_id' => $transfer->id,
                'transfer_number' => $transfer->transfer_number,
            ]);
        });
    }

    /**
     * Post stock count adjustments to GL
     */
    public function postStockCountAdjustments(\Gopos\Models\StockCount $count): void
    {
        DB::transaction(function () use ($count) {
            $totalAdjustmentValue = 0;

            foreach ($count->items as $item) {
                $variance = $item->counted_quantity - $item->system_quantity;
                if ($variance != 0) {
                    $adjustmentValue = $variance * $item->product->cost;
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
                $inventoryAccount = $this->glService->getAccountByCode('1300');
                $adjustmentAccount = $this->glService->getAccountByCode('5099'); // Inventory Adjustment

                $entries = [];
                if ($totalAdjustmentValue > 0) {
                    // Inventory increase
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
                    // Inventory decrease
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

            Log::info('Stock count adjustments posted', [
                'count_id' => $count->id,
                'adjustment_value' => $totalAdjustmentValue,
            ]);
        });
    }
}
