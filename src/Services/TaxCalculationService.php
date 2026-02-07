<?php

namespace Gopos\Services;

use Gopos\Models\Customer;
use Gopos\Models\Product;
use Gopos\Models\TaxCode;
use Gopos\Models\TaxExemption;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class TaxCalculationService
{
    /**
     * Calculate tax for a line item
     */
    public function calculateLineTax(float $amount, ?TaxCode $taxCode): array
    {
        if (! $taxCode || ! $taxCode->is_active) {
            return [
                'net_amount' => $amount,
                'tax_amount' => 0,
                'gross_amount' => $amount,
                'tax_rate' => 0,
            ];
        }

        return $taxCode->calculateTax($amount);
    }

    /**
     * Calculate total tax for a transaction with multiple items
     */
    public function calculateTransactionTax(array $items, ?TaxCode $defaultTaxCode = null): array
    {
        $totalNet = 0;
        $totalTax = 0;
        $totalGross = 0;
        $breakdown = [];

        foreach ($items as $item) {
            $taxCode = $item['tax_code'] ?? $defaultTaxCode;
            $amount = $item['amount'] ?? 0;

            $result = $this->calculateLineTax($amount, $taxCode);

            $totalNet += $result['net_amount'];
            $totalTax += $result['tax_amount'];
            $totalGross += $result['gross_amount'];

            // Group by tax code for breakdown
            if ($taxCode) {
                $key = $taxCode->code;
                if (! isset($breakdown[$key])) {
                    $breakdown[$key] = [
                        'tax_code' => $taxCode->code,
                        'tax_name' => $taxCode->name,
                        'tax_rate' => $taxCode->rate,
                        'net_amount' => 0,
                        'tax_amount' => 0,
                    ];
                }
                $breakdown[$key]['net_amount'] += $result['net_amount'];
                $breakdown[$key]['tax_amount'] += $result['tax_amount'];
            }
        }

        return [
            'net_amount' => round($totalNet, 2),
            'tax_amount' => round($totalTax, 2),
            'gross_amount' => round($totalGross, 2),
            'breakdown' => array_values($breakdown),
        ];
    }

    /**
     * Get the applicable tax code for a product considering exemptions
     */
    public function getApplicableTaxCode(Product $product, ?Customer $customer = null): ?TaxCode
    {
        // Check if product is tax exempt
        if ($product->is_tax_exempt) {
            return null;
        }

        // Check for customer-specific exemption
        if ($customer) {
            $exemption = TaxExemption::query()
                ->where('exemptable_type', Customer::class)
                ->where('exemptable_id', $customer->id)
                ->valid()
                ->first();

            if ($exemption) {
                return null;
            }
        }

        // Check for product-specific exemption
        $productExemption = TaxExemption::query()
            ->where('exemptable_type', Product::class)
            ->where('exemptable_id', $product->id)
            ->valid()
            ->first();

        if ($productExemption) {
            return null;
        }

        // Return the product's default tax code
        return $product->defaultTaxCode;
    }

    /**
     * Check if an entity is exempt from a specific tax
     */
    public function isExempt(Model $entity, TaxCode $taxCode): bool
    {
        return TaxExemption::query()
            ->where('tax_code_id', $taxCode->id)
            ->forEntity($entity)
            ->valid()
            ->exists();
    }

    /**
     * Get all valid exemptions for an entity
     */
    public function getExemptions(Model $entity): \Illuminate\Database\Eloquent\Collection
    {
        return TaxExemption::query()
            ->forEntity($entity)
            ->valid()
            ->with('taxCode')
            ->get();
    }

    /**
     * Calculate tax for a sale
     */
    public function calculateSaleTax(array $items, ?Customer $customer = null): array
    {
        $processedItems = [];

        foreach ($items as $item) {
            $product = $item['product'] ?? null;
            $amount = $item['amount'] ?? 0;
            $quantity = $item['quantity'] ?? 1;

            $taxCode = null;
            if ($product instanceof Product) {
                $taxCode = $this->getApplicableTaxCode($product, $customer);
            } elseif (isset($item['tax_code']) && $item['tax_code'] instanceof TaxCode) {
                $taxCode = $item['tax_code'];
            }

            $processedItems[] = [
                'product' => $product,
                'amount' => $amount * $quantity,
                'tax_code' => $taxCode,
            ];
        }

        return $this->calculateTransactionTax($processedItems);
    }

    /**
     * Get tax summary for a date range
     */
    public function getTaxSummary(string $startDate, string $endDate): array
    {
        // This would aggregate tax data from sales and purchases
        // Implementation depends on your specific reporting needs

        return [
            'sales_tax_collected' => 0,
            'purchases_tax_paid' => 0,
            'net_tax_liability' => 0,
            'by_tax_code' => [],
        ];
    }

    // =========================================================================
    // POS-Specific Methods
    // =========================================================================

    /**
     * Calculate tax for POS cart items.
     */
    public function calculateCartTax(
        array $items,
        ?TaxCode $defaultTaxCode = null,
        ?Customer $customer = null
    ): array {
        $taxBreakdown = [];
        $totalNet = 0;
        $totalTax = 0;
        $totalGross = 0;
        $processedItems = [];

        foreach ($items as $item) {
            $product = $item['product'] ?? (isset($item['product_id']) ? Product::find($item['product_id']) : null);
            $quantity = (int) ($item['quantity'] ?? 1);
            $unitPrice = (float) ($item['unit_price'] ?? $item['price'] ?? $product?->sale_price ?? 0);

            $itemTaxCode = isset($item['tax_code_id'])
                ? TaxCode::find($item['tax_code_id'])
                : ($product ? $this->getApplicableTaxCode($product, $customer) : $defaultTaxCode);

            $itemResult = $this->calculateItemTax($product, $unitPrice, $quantity, $itemTaxCode, $customer);

            $processedItems[] = array_merge($item, $itemResult);

            $totalNet += $itemResult['net_amount'];
            $totalTax += $itemResult['tax_amount'];
            $totalGross += $itemResult['gross_amount'];

            // Group by tax code for breakdown
            $taxCodeId = $itemResult['tax_code_id'] ?? 'no_tax';
            if (! isset($taxBreakdown[$taxCodeId])) {
                $taxBreakdown[$taxCodeId] = [
                    'tax_code_id' => $itemResult['tax_code_id'] ?? null,
                    'tax_code_name' => $itemResult['tax_code_name'] ?? __('No Tax'),
                    'tax_rate' => $itemResult['tax_rate'] ?? 0,
                    'taxable_amount' => 0,
                    'tax_amount' => 0,
                ];
            }

            $taxBreakdown[$taxCodeId]['taxable_amount'] += $itemResult['net_amount'];
            $taxBreakdown[$taxCodeId]['tax_amount'] += $itemResult['tax_amount'];
        }

        return [
            'items' => $processedItems,
            'sub_total' => round($totalNet, 2),
            'total_tax' => round($totalTax, 2),
            'grand_total' => round($totalGross, 2),
            'tax_breakdown' => array_values($taxBreakdown),
        ];
    }

    /**
     * Calculate tax for a single item.
     */
    public function calculateItemTax(
        ?Product $product,
        float $unitPrice,
        int $quantity,
        ?TaxCode $taxCode = null,
        ?Customer $customer = null
    ): array {
        if (! $taxCode) {
            return $this->noTaxResult($unitPrice, $quantity);
        }

        // Check for exemptions
        if ($product && $this->isProductExempt($product, $customer, $taxCode)) {
            return $this->noTaxResult($unitPrice, $quantity);
        }

        $lineTotal = $unitPrice * $quantity;
        $taxResult = $taxCode->calculateTax($lineTotal);

        return [
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'line_total' => $lineTotal,
            'net_amount' => $taxResult['net_amount'],
            'tax_amount' => $taxResult['tax_amount'],
            'gross_amount' => $taxResult['gross_amount'],
            'tax_rate' => $taxCode->rate,
            'tax_code_id' => $taxCode->id,
            'tax_code_name' => $taxCode->localizedName,
            'is_inclusive' => $taxCode->type === 'inclusive',
        ];
    }

    /**
     * Check if product is exempt from specific tax.
     */
    public function isProductExempt(?Product $product, ?Customer $customer, TaxCode $taxCode): bool
    {
        if (! $product) {
            return false;
        }

        // Check customer exemption
        if ($customer) {
            $customerExemption = TaxExemption::valid()
                ->forEntity($customer)
                ->where('tax_code_id', $taxCode->id)
                ->first();

            if ($customerExemption) {
                return true;
            }
        }

        // Check product exemption
        $productExemption = TaxExemption::valid()
            ->forEntity($product)
            ->where('tax_code_id', $taxCode->id)
            ->first();

        if ($productExemption) {
            return true;
        }

        // Check category exemption
        if ($product->category) {
            $categoryExemption = TaxExemption::valid()
                ->forEntity($product->category)
                ->where('tax_code_id', $taxCode->id)
                ->first();

            if ($categoryExemption) {
                return true;
            }
        }

        return false;
    }

    /**
     * Apply discount and recalculate tax.
     */
    public function applyDiscountWithTax(
        array $cartTax,
        float $discountAmount,
        string $discountType = 'amount'
    ): array {
        $subTotal = $cartTax['sub_total'];

        if ($discountType === 'percentage') {
            $discountAmount = $subTotal * ($discountAmount / 100);
        }

        $discountAmount = min($discountAmount, $subTotal);

        // Recalculate tax on discounted amount
        $discountedSubTotal = $subTotal - $discountAmount;
        $discountRatio = $subTotal > 0 ? ($discountedSubTotal / $subTotal) : 0;

        // Proportionally reduce tax
        $discountedTax = $cartTax['total_tax'] * $discountRatio;
        $discountedGrandTotal = $discountedSubTotal + $discountedTax;

        return [
            'original_sub_total' => $cartTax['sub_total'],
            'discount_amount' => round($discountAmount, 2),
            'sub_total' => round($discountedSubTotal, 2),
            'total_tax' => round($discountedTax, 2),
            'grand_total' => round($discountedGrandTotal, 2),
            'tax_breakdown' => array_map(function ($tax) use ($discountRatio) {
                return [
                    'tax_code_id' => $tax['tax_code_id'],
                    'tax_code_name' => $tax['tax_code_name'],
                    'tax_rate' => $tax['tax_rate'],
                    'taxable_amount' => round($tax['taxable_amount'] * $discountRatio, 2),
                    'tax_amount' => round($tax['tax_amount'] * $discountRatio, 2),
                ];
            }, $cartTax['tax_breakdown'] ?? []),
        ];
    }

    /**
     * Return zero tax result.
     */
    protected function noTaxResult(float $unitPrice, int $quantity): array
    {
        $lineTotal = $unitPrice * $quantity;

        return [
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'line_total' => $lineTotal,
            'net_amount' => $lineTotal,
            'tax_amount' => 0,
            'gross_amount' => $lineTotal,
            'tax_rate' => 0,
            'tax_code_id' => null,
            'tax_code_name' => __('No Tax'),
            'is_inclusive' => false,
        ];
    }

    /**
     * Get default tax code for sales.
     */
    public function getDefaultTaxCode(): ?TaxCode
    {
        return TaxCode::active()->forSales()->first();
    }

    /**
     * Get all active tax codes for sales.
     */
    public function getAvailableTaxCodes(): Collection
    {
        return TaxCode::active()->forSales()->get();
    }

    /**
     * Format tax for display on receipt.
     */
    public function formatTaxForReceipt(array $taxBreakdown): array
    {
        $formatted = [];

        foreach ($taxBreakdown as $tax) {
            if ($tax['tax_amount'] > 0) {
                $formatted[] = [
                    'label' => sprintf('%s (%s%%)', $tax['tax_code_name'], number_format($tax['tax_rate'] * 100, 2)),
                    'amount' => $tax['tax_amount'],
                ];
            }
        }

        return $formatted;
    }

    /**
     * Calculate reverse tax (extract tax from inclusive price).
     */
    public function extractTaxFromInclusive(float $inclusiveAmount, float $taxRate): array
    {
        $netAmount = $inclusiveAmount / (1 + $taxRate);
        $taxAmount = $inclusiveAmount - $netAmount;

        return [
            'net_amount' => round($netAmount, 2),
            'tax_amount' => round($taxAmount, 2),
            'gross_amount' => round($inclusiveAmount, 2),
        ];
    }

    /**
     * Calculate tax to add on exclusive price.
     */
    public function addTaxToExclusive(float $exclusiveAmount, float $taxRate): array
    {
        $taxAmount = $exclusiveAmount * $taxRate;
        $grossAmount = $exclusiveAmount + $taxAmount;

        return [
            'net_amount' => round($exclusiveAmount, 2),
            'tax_amount' => round($taxAmount, 2),
            'gross_amount' => round($grossAmount, 2),
        ];
    }
}
