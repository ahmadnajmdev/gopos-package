<?php

namespace Gopos\Listeners;

use Gopos\Events\SaleCreated;
use Gopos\Services\GeneralLedgerService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class PostSaleToGL implements ShouldQueue
{
    public function __construct(
        protected GeneralLedgerService $glService
    ) {}

    public function handle(SaleCreated $event): void
    {
        $sale = $event->sale;
        $entries = [];

        // Debit: Cash/Accounts Receivable
        $debitAccount = $sale->payment_status === 'paid'
            ? $this->glService->getAccountByCode('1001') // Cash
            : $this->glService->getAccountByCode('1200'); // Accounts Receivable

        if (! $debitAccount) {
            Log::warning('GL account not found for sale posting', ['sale_id' => $sale->id]);

            return;
        }

        $entries[] = [
            'account_id' => $debitAccount->id,
            'debit' => $sale->total_amount,
            'credit' => 0,
            'description' => "Sale #{$sale->sale_number}",
        ];

        // Credit: Sales Revenue
        $salesAccount = $this->glService->getAccountByCode('4001');
        if ($salesAccount) {
            $entries[] = [
                'account_id' => $salesAccount->id,
                'debit' => 0,
                'credit' => $sale->sub_total,
                'description' => "Sales revenue - #{$sale->sale_number}",
            ];
        }

        // Credit: Tax Payable (if applicable)
        if ($sale->tax_amount > 0) {
            $taxAccount = $this->glService->getAccountByCode('2100');
            if ($taxAccount) {
                $entries[] = [
                    'account_id' => $taxAccount->id,
                    'debit' => 0,
                    'credit' => $sale->tax_amount,
                    'description' => "Tax payable - Sale #{$sale->sale_number}",
                ];
            }
        }

        // Debit: Sales Discount (if applicable)
        if ($sale->discount_amount > 0) {
            $discountAccount = $this->glService->getAccountByCode('4002');
            if ($discountAccount) {
                $entries[] = [
                    'account_id' => $discountAccount->id,
                    'debit' => $sale->discount_amount,
                    'credit' => 0,
                    'description' => "Sales discount - #{$sale->sale_number}",
                ];
            }
        }

        if (count($entries) > 0) {
            $this->glService->createJournalEntry(
                date: $sale->sale_date,
                description: "Sale #{$sale->sale_number}",
                lines: $entries,
                referenceType: 'sale',
                referenceId: $sale->id
            );
        }

        // Post COGS entry
        $this->postCOGSEntry($sale);

        Log::info('Sale posted to GL', [
            'sale_id' => $sale->id,
            'sale_number' => $sale->sale_number,
        ]);
    }

    protected function postCOGSEntry($sale): void
    {
        $totalCost = 0;

        foreach ($sale->items as $item) {
            $product = $item->product;
            if ($product) {
                $totalCost += ($product->cost ?? 0) * $item->quantity;
            }
        }

        if ($totalCost <= 0) {
            return;
        }

        $cogsAccount = $this->glService->getAccountByCode('5001');
        $inventoryAccount = $this->glService->getAccountByCode('1300');

        if (! $cogsAccount || ! $inventoryAccount) {
            return;
        }

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
}
