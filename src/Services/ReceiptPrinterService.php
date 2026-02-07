<?php

namespace Gopos\Services;

use Gopos\Models\Sale;
use Gopos\Models\SalePayment;

class ReceiptPrinterService
{
    protected array $config;

    public function __construct()
    {
        $this->config = [
            'paper_width' => config('pos.receipt_width', 48), // characters
            'currency_symbol' => config('pos.currency_symbol', 'IQD'),
            'business_name' => config('app.name', 'GoPOS'),
            'show_tax_breakdown' => true,
            'show_loyalty_points' => true,
            'footer_message' => __('Thank you for your business!'),
        ];
    }

    /**
     * Generate receipt content for a sale.
     */
    public function generateReceipt(Sale $sale, array $options = []): array
    {
        $config = array_merge($this->config, $options);

        return [
            'header' => $this->generateHeader($sale, $config),
            'items' => $this->generateItems($sale),
            'totals' => $this->generateTotals($sale),
            'payments' => $this->generatePayments($sale),
            'footer' => $this->generateFooter($sale, $config),
            'raw_text' => $this->generateRawText($sale, $config),
            'html' => $this->generateHtml($sale, $config),
            'escpos' => $this->generateEscPos($sale, $config),
        ];
    }

    /**
     * Generate receipt header.
     */
    protected function generateHeader(Sale $sale, array $config): array
    {
        return [
            'business_name' => $config['business_name'],
            'receipt_number' => $sale->sale_number,
            'date' => $sale->sale_date->format('Y-m-d'),
            'time' => $sale->created_at->format('H:i:s'),
            'cashier' => $sale->posSession?->user?->name ?? auth()->user()?->name,
            'terminal_id' => $sale->posSession?->terminal_id,
            'customer_name' => $sale->customer?->name ?? __('Walk-in Customer'),
        ];
    }

    /**
     * Generate receipt items.
     */
    protected function generateItems(Sale $sale): array
    {
        return $sale->items->map(function ($item) {
            $quantity = $item->stock ?? $item->quantity ?? 1;
            $unitPrice = $item->price ?? $item->unit_price ?? 0;
            $lineTotal = $item->total_amount ?? $item->line_total ?? ($quantity * $unitPrice);

            return [
                'name' => $item->product?->name ?? $item->product_name ?? 'Unknown',
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount' => $item->discount_amount ?? $item->discount ?? 0,
                'tax_amount' => $item->tax_amount ?? 0,
                'line_total' => $lineTotal,
                'formatted_line' => sprintf(
                    '%s x %s = %s',
                    $item->product?->name ?? $item->product_name ?? 'Unknown',
                    number_format($quantity, 0),
                    number_format($lineTotal, 2)
                ),
            ];
        })->toArray();
    }

    /**
     * Generate totals section.
     */
    protected function generateTotals(Sale $sale): array
    {
        return [
            'sub_total' => $sale->sub_total,
            'discount' => $sale->discount ?? 0,
            'tax_amount' => $sale->tax_amount ?? 0,
            'total' => $sale->total_amount,
            'formatted' => [
                'sub_total' => number_format($sale->sub_total, 2),
                'discount' => number_format($sale->discount ?? 0, 2),
                'tax' => number_format($sale->tax_amount ?? 0, 2),
                'total' => number_format($sale->total_amount, 2),
            ],
        ];
    }

    /**
     * Generate payments section.
     */
    protected function generatePayments(Sale $sale): array
    {
        $payments = $sale->payments;

        if ($payments->isEmpty()) {
            // Single payment mode (legacy)
            return [
                [
                    'method' => __('Payment'),
                    'amount' => $sale->paid_amount,
                    'tendered' => $sale->paid_amount,
                    'change' => 0,
                ],
            ];
        }

        return $payments->map(function (SalePayment $payment) {
            return [
                'method' => $payment->localizedPaymentMethod,
                'amount' => $payment->amount,
                'tendered' => $payment->tendered_amount,
                'change' => $payment->change_amount,
                'reference' => $payment->reference_number,
            ];
        })->toArray();
    }

    /**
     * Generate footer section.
     */
    protected function generateFooter(Sale $sale, array $config): array
    {
        $footer = [
            'message' => $config['footer_message'],
            'barcode' => $sale->sale_number,
        ];

        // Add loyalty info if applicable
        if ($config['show_loyalty_points'] && $sale->customer) {
            $loyaltyTransaction = $sale->loyaltyTransactions()->where('type', 'earn')->first();
            if ($loyaltyTransaction) {
                $footer['loyalty'] = [
                    'points_earned' => $loyaltyTransaction->points,
                    'message' => __('You earned :points points!', ['points' => $loyaltyTransaction->points]),
                ];
            }
        }

        return $footer;
    }

    /**
     * Generate raw text receipt (for text-only printers).
     */
    public function generateRawText(Sale $sale, ?array $config = null): string
    {
        $config = $config ?? $this->config;
        $width = $config['paper_width'];
        $lines = [];

        // Header
        $lines[] = $this->centerText($config['business_name'], $width);
        $lines[] = str_repeat('-', $width);
        $lines[] = __('Receipt').': '.$sale->sale_number;
        $lines[] = __('Date').': '.$sale->sale_date->format('Y-m-d H:i');
        $lines[] = __('Cashier').': '.($sale->posSession?->user?->name ?? 'N/A');

        if ($sale->customer) {
            $lines[] = __('Customer').': '.$sale->customer->name;
        }

        $lines[] = str_repeat('-', $width);

        // Items
        foreach ($sale->items as $item) {
            $quantity = $item->stock ?? $item->quantity ?? 1;
            $unitPrice = $item->price ?? $item->unit_price ?? 0;
            $lineTotal = $item->total_amount ?? $item->line_total ?? ($quantity * $unitPrice);

            $name = mb_substr($item->product?->name ?? $item->product_name ?? 'Unknown', 0, $width - 15);
            $lines[] = $name;
            $lines[] = sprintf(
                '  %s x %s = %s',
                number_format($quantity, 0),
                number_format($unitPrice, 2),
                number_format($lineTotal, 2)
            );
        }

        $lines[] = str_repeat('-', $width);

        // Totals
        $lines[] = $this->alignLR(__('Subtotal'), number_format($sale->sub_total, 2), $width);

        if ($sale->discount > 0) {
            $lines[] = $this->alignLR(__('Discount'), '-'.number_format($sale->discount, 2), $width);
        }

        if ($sale->tax_amount > 0) {
            $lines[] = $this->alignLR(__('Tax'), number_format($sale->tax_amount, 2), $width);
        }

        $lines[] = str_repeat('=', $width);
        $lines[] = $this->alignLR(__('TOTAL'), number_format($sale->total_amount, 2), $width);
        $lines[] = str_repeat('=', $width);

        // Payments
        $payments = $sale->payments;
        if ($payments->isNotEmpty()) {
            foreach ($payments as $payment) {
                $lines[] = $this->alignLR(
                    $payment->localizedPaymentMethod,
                    number_format($payment->amount, 2),
                    $width
                );
                if ($payment->change_amount > 0) {
                    $lines[] = $this->alignLR(__('Change'), number_format($payment->change_amount, 2), $width);
                }
            }
        } else {
            $lines[] = $this->alignLR(__('Paid'), number_format($sale->paid_amount, 2), $width);
        }

        $lines[] = str_repeat('-', $width);

        // Footer
        $lines[] = '';
        $lines[] = $this->centerText($config['footer_message'], $width);
        $lines[] = '';

        return implode("\n", $lines);
    }

    /**
     * Generate HTML receipt for browser printing.
     */
    public function generateHtml(Sale $sale, ?array $config = null): string
    {
        $config = $config ?? $this->config;

        $html = '<div class="receipt" style="font-family: monospace; width: 300px; margin: 0 auto;">';

        // Header
        $html .= '<div style="text-align: center; margin-bottom: 10px;">';
        $html .= '<h2 style="margin: 0;">'.e($config['business_name']).'</h2>';
        $html .= '</div>';

        $html .= '<div style="margin-bottom: 10px;">';
        $html .= '<div>'.__('Receipt').': '.e($sale->sale_number).'</div>';
        $html .= '<div>'.__('Date').': '.$sale->sale_date->format('Y-m-d H:i').'</div>';
        $html .= '<div>'.__('Cashier').': '.e($sale->posSession?->user?->name ?? 'N/A').'</div>';
        if ($sale->customer) {
            $html .= '<div>'.__('Customer').': '.e($sale->customer->name).'</div>';
        }
        $html .= '</div>';

        $html .= '<hr style="border-style: dashed;">';

        // Items
        $html .= '<table style="width: 100%; font-size: 12px;">';
        foreach ($sale->items as $item) {
            $quantity = $item->stock ?? $item->quantity ?? 1;
            $unitPrice = $item->price ?? $item->unit_price ?? 0;
            $lineTotal = $item->total_amount ?? $item->line_total ?? ($quantity * $unitPrice);

            $html .= '<tr>';
            $html .= '<td colspan="2">'.e($item->product?->name ?? $item->product_name ?? 'Unknown').'</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td>'.number_format($quantity, 0).' x '.number_format($unitPrice, 2).'</td>';
            $html .= '<td style="text-align: right;">'.number_format($lineTotal, 2).'</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';

        $html .= '<hr style="border-style: dashed;">';

        // Totals
        $html .= '<table style="width: 100%;">';
        $html .= '<tr><td>'.__('Subtotal').'</td><td style="text-align: right;">'.number_format($sale->sub_total, 2).'</td></tr>';

        if ($sale->discount > 0) {
            $html .= '<tr><td>'.__('Discount').'</td><td style="text-align: right;">-'.number_format($sale->discount, 2).'</td></tr>';
        }

        if ($sale->tax_amount > 0) {
            $html .= '<tr><td>'.__('Tax').'</td><td style="text-align: right;">'.number_format($sale->tax_amount, 2).'</td></tr>';
        }

        $html .= '<tr style="font-weight: bold; font-size: 14px;">';
        $html .= '<td>'.__('TOTAL').'</td>';
        $html .= '<td style="text-align: right;">'.number_format($sale->total_amount, 2).'</td>';
        $html .= '</tr>';
        $html .= '</table>';

        $html .= '<hr>';

        // Payments
        $payments = $sale->payments;
        if ($payments->isNotEmpty()) {
            $html .= '<table style="width: 100%;">';
            foreach ($payments as $payment) {
                $html .= '<tr><td>'.e($payment->localizedPaymentMethod).'</td><td style="text-align: right;">'.number_format($payment->amount, 2).'</td></tr>';
                if ($payment->change_amount > 0) {
                    $html .= '<tr><td>'.__('Change').'</td><td style="text-align: right;">'.number_format($payment->change_amount, 2).'</td></tr>';
                }
            }
            $html .= '</table>';
        }

        $html .= '<hr style="border-style: dashed;">';

        // Footer
        $html .= '<div style="text-align: center; margin-top: 10px;">';
        $html .= '<p>'.e($config['footer_message']).'</p>';
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }

    /**
     * Generate ESC/POS commands for thermal printers.
     */
    public function generateEscPos(Sale $sale, ?array $config = null): array
    {
        $config = $config ?? $this->config;

        // ESC/POS command constants
        $ESC = "\x1B";
        $GS = "\x1D";

        $commands = [];

        // Initialize printer
        $commands[] = $ESC.'@'; // Initialize

        // Center alignment
        $commands[] = $ESC.'a'."\x01"; // Center

        // Bold + Double height for business name
        $commands[] = $ESC.'E'."\x01"; // Bold on
        $commands[] = $GS.'!'."\x11"; // Double height + width
        $commands[] = $config['business_name']."\n";
        $commands[] = $GS.'!'."\x00"; // Normal size
        $commands[] = $ESC.'E'."\x00"; // Bold off

        // Left alignment
        $commands[] = $ESC.'a'."\x00";

        // Receipt info
        $commands[] = __('Receipt').': '.$sale->sale_number."\n";
        $commands[] = __('Date').': '.$sale->sale_date->format('Y-m-d H:i')."\n";
        $commands[] = __('Cashier').': '.($sale->posSession?->user?->name ?? 'N/A')."\n";

        if ($sale->customer) {
            $commands[] = __('Customer').': '.$sale->customer->name."\n";
        }

        // Separator
        $commands[] = str_repeat('-', 32)."\n";

        // Items
        foreach ($sale->items as $item) {
            $quantity = $item->stock ?? $item->quantity ?? 1;
            $unitPrice = $item->price ?? $item->unit_price ?? 0;
            $lineTotal = $item->total_amount ?? $item->line_total ?? ($quantity * $unitPrice);

            $name = mb_substr($item->product?->name ?? $item->product_name ?? 'Unknown', 0, 20);
            $commands[] = $name."\n";
            $commands[] = sprintf(
                "  %s x %s = %s\n",
                number_format($quantity, 0),
                number_format($unitPrice, 2),
                number_format($lineTotal, 2)
            );
        }

        $commands[] = str_repeat('-', 32)."\n";

        // Totals
        $commands[] = sprintf("%-16s %15s\n", __('Subtotal'), number_format($sale->sub_total, 2));

        if ($sale->discount > 0) {
            $commands[] = sprintf("%-16s %15s\n", __('Discount'), '-'.number_format($sale->discount, 2));
        }

        if ($sale->tax_amount > 0) {
            $commands[] = sprintf("%-16s %15s\n", __('Tax'), number_format($sale->tax_amount, 2));
        }

        $commands[] = str_repeat('=', 32)."\n";

        // Bold total
        $commands[] = $ESC.'E'."\x01";
        $commands[] = sprintf("%-16s %15s\n", __('TOTAL'), number_format($sale->total_amount, 2));
        $commands[] = $ESC.'E'."\x00";

        $commands[] = str_repeat('=', 32)."\n";

        // Payments
        $payments = $sale->payments;
        if ($payments->isNotEmpty()) {
            foreach ($payments as $payment) {
                $commands[] = sprintf("%-16s %15s\n", $payment->localizedPaymentMethod, number_format($payment->amount, 2));
                if ($payment->change_amount > 0) {
                    $commands[] = sprintf("%-16s %15s\n", __('Change'), number_format($payment->change_amount, 2));
                }
            }
        }

        $commands[] = str_repeat('-', 32)."\n";

        // Footer - centered
        $commands[] = $ESC.'a'."\x01";
        $commands[] = "\n".$config['footer_message']."\n\n";

        // Cut paper
        $commands[] = $GS.'V'."\x00"; // Full cut

        return [
            'commands' => implode('', $commands),
            'base64' => base64_encode(implode('', $commands)),
        ];
    }

    /**
     * Center text helper.
     */
    protected function centerText(string $text, int $width): string
    {
        $textLength = mb_strlen($text);
        if ($textLength >= $width) {
            return $text;
        }

        $padding = (int) floor(($width - $textLength) / 2);

        return str_repeat(' ', $padding).$text;
    }

    /**
     * Left-right align helper.
     */
    protected function alignLR(string $left, string $right, int $width): string
    {
        $leftLen = mb_strlen($left);
        $rightLen = mb_strlen($right);
        $spaces = $width - $leftLen - $rightLen;

        if ($spaces < 1) {
            $spaces = 1;
        }

        return $left.str_repeat(' ', $spaces).$right;
    }

    /**
     * Get printer configuration.
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Update printer configuration.
     */
    public function setConfig(array $config): self
    {
        $this->config = array_merge($this->config, $config);

        return $this;
    }
}
