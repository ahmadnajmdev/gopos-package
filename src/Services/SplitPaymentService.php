<?php

namespace Gopos\Services;

use Gopos\Models\Currency;
use Gopos\Models\PosSession;
use Gopos\Models\Sale;
use Gopos\Models\SalePayment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SplitPaymentService
{
    protected POSSessionService $sessionService;

    public function __construct(POSSessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    /**
     * Process multiple payments for a sale.
     *
     * @param  array  $payments  Array of payment data [['method' => 'cash', 'amount' => 100, 'tendered' => 150], ...]
     */
    public function processPayments(Sale $sale, array $payments, ?PosSession $session = null): Collection
    {
        $session = $session ?? $this->sessionService->getCurrentSession();

        return DB::transaction(function () use ($sale, $payments, $session) {
            $processedPayments = collect();
            $totalPaid = 0;

            foreach ($payments as $paymentData) {
                $payment = $this->createPayment($sale, $paymentData, $session);
                $processedPayments->push($payment);
                $totalPaid += $payment->amount;

                // Record transaction in session if exists
                if ($session && $session->status === 'open') {
                    $this->sessionService->recordTransaction(
                        $session,
                        'sale',
                        $payment->amount,
                        $payment->payment_method,
                        $payment->currency,
                        [
                            'reference_type' => Sale::class,
                            'reference_id' => $sale->id,
                        ]
                    );
                }
            }

            // Update sale paid amount
            $sale->update([
                'paid_amount' => $totalPaid,
                'status' => $totalPaid >= $sale->total_amount ? 'paid' : 'partial',
            ]);

            return $processedPayments;
        });
    }

    /**
     * Create a single payment record.
     */
    public function createPayment(Sale $sale, array $paymentData, ?PosSession $session = null): SalePayment
    {
        $method = $paymentData['method'] ?? 'cash';
        $amount = (float) ($paymentData['amount'] ?? 0);
        $tendered = (float) ($paymentData['tendered'] ?? $amount);
        $currency = isset($paymentData['currency_id'])
            ? Currency::find($paymentData['currency_id'])
            : ($sale->currency ?? Currency::getBaseCurrency());

        $change = $method === 'cash' ? max(0, $tendered - $amount) : 0;

        $amountInBase = $currency
            ? $currency->convertFromCurrency($amount, $currency->code)
            : $amount;

        return SalePayment::create([
            'sale_id' => $sale->id,
            'pos_session_id' => $session?->id,
            'payment_method' => $method,
            'amount' => $amount,
            'currency_id' => $currency?->id,
            'exchange_rate' => $currency?->exchange_rate ?? 1,
            'amount_in_base_currency' => $amountInBase,
            'reference_number' => $paymentData['reference_number'] ?? null,
            'tendered_amount' => $tendered,
            'change_amount' => $change,
            'notes' => $paymentData['notes'] ?? null,
        ]);
    }

    /**
     * Add additional payment to existing sale.
     */
    public function addPayment(Sale $sale, array $paymentData, ?PosSession $session = null): SalePayment
    {
        $session = $session ?? $this->sessionService->getCurrentSession();

        return DB::transaction(function () use ($sale, $paymentData, $session) {
            $payment = $this->createPayment($sale, $paymentData, $session);

            // Record in session
            if ($session && $session->status === 'open') {
                $this->sessionService->recordTransaction(
                    $session,
                    'sale',
                    $payment->amount,
                    $payment->payment_method,
                    $payment->currency,
                    [
                        'reference_type' => Sale::class,
                        'reference_id' => $sale->id,
                    ]
                );
            }

            // Update sale
            $newPaidAmount = $sale->paid_amount + $payment->amount;
            $sale->update([
                'paid_amount' => $newPaidAmount,
                'status' => $newPaidAmount >= $sale->total_amount ? 'paid' : 'partial',
            ]);

            return $payment;
        });
    }

    /**
     * Calculate change for cash payment.
     */
    public function calculateChange(float $tenderedAmount, float $dueAmount): float
    {
        return max(0, $tenderedAmount - $dueAmount);
    }

    /**
     * Validate split payment totals.
     */
    public function validatePayments(float $saleTotal, array $payments): array
    {
        $errors = [];
        $totalPayment = 0;

        foreach ($payments as $index => $payment) {
            $amount = (float) ($payment['amount'] ?? 0);

            if ($amount <= 0) {
                $errors[] = __('Payment :index has invalid amount.', ['index' => $index + 1]);
            }

            $totalPayment += $amount;
        }

        if ($totalPayment < $saleTotal) {
            $remaining = $saleTotal - $totalPayment;
            $errors[] = __('Total payments (:paid) are less than sale total (:total). Remaining: :remaining', [
                'paid' => number_format($totalPayment, 2),
                'total' => number_format($saleTotal, 2),
                'remaining' => number_format($remaining, 2),
            ]);
        }

        return $errors;
    }

    /**
     * Calculate remaining amount for partial payments.
     */
    public function getRemainingAmount(Sale $sale): float
    {
        return max(0, $sale->total_amount - $sale->paid_amount);
    }

    /**
     * Get payment breakdown for a sale.
     */
    public function getPaymentBreakdown(Sale $sale): array
    {
        $payments = $sale->payments;

        $breakdown = [
            'total_sale' => $sale->total_amount,
            'total_paid' => $sale->paid_amount,
            'remaining' => $this->getRemainingAmount($sale),
            'is_fully_paid' => $sale->isPaid(),
            'payment_count' => $payments->count(),
            'by_method' => [],
        ];

        foreach ($payments->groupBy('payment_method') as $method => $methodPayments) {
            $breakdown['by_method'][$method] = [
                'count' => $methodPayments->count(),
                'total' => $methodPayments->sum('amount'),
                'localized_name' => __($this->getPaymentMethodLabel($method)),
            ];
        }

        return $breakdown;
    }

    /**
     * Get localized payment method label.
     */
    public function getPaymentMethodLabel(string $method): string
    {
        return match ($method) {
            'cash' => __('Cash'),
            'card' => __('Card'),
            'bank_transfer' => __('Bank Transfer'),
            'mobile_payment' => __('Mobile Payment'),
            'credit' => __('Credit'),
            default => $method,
        };
    }

    /**
     * Void/refund a specific payment.
     */
    public function voidPayment(SalePayment $payment, ?PosSession $session = null): bool
    {
        $session = $session ?? $this->sessionService->getCurrentSession();

        return DB::transaction(function () use ($payment, $session) {
            $sale = $payment->sale;

            // Record refund in session
            if ($session && $session->status === 'open') {
                $this->sessionService->recordRefundTransaction(
                    $session,
                    $payment,
                    $payment->payment_method,
                    $payment->amount
                );
            }

            // Update sale paid amount
            $newPaidAmount = max(0, $sale->paid_amount - $payment->amount);
            $sale->update([
                'paid_amount' => $newPaidAmount,
                'status' => $newPaidAmount <= 0 ? 'pending' : ($newPaidAmount >= $sale->total_amount ? 'paid' : 'partial'),
            ]);

            // Delete the payment
            $payment->delete();

            return true;
        });
    }

    /**
     * Get available payment methods with their labels.
     */
    public function getAvailablePaymentMethods(): array
    {
        return SalePayment::getPaymentMethods();
    }

    /**
     * Suggest optimal split for a given amount.
     * Useful for quick payment suggestions.
     */
    public function suggestSplit(float $total, array $availableAmounts = []): array
    {
        // Default cash denominations for Iraq/Kurdistan (IQD)
        if (empty($availableAmounts)) {
            $availableAmounts = [50000, 25000, 10000, 5000, 1000, 500, 250];
        }

        $suggestions = [];

        // Exact amount suggestion
        $suggestions[] = [
            'method' => 'cash',
            'amount' => $total,
            'tendered' => $total,
            'change' => 0,
        ];

        // Round up suggestions
        foreach ($availableAmounts as $denomination) {
            if ($denomination > $total) {
                $suggestions[] = [
                    'method' => 'cash',
                    'amount' => $total,
                    'tendered' => $denomination,
                    'change' => $denomination - $total,
                ];
                break;
            }
        }

        return $suggestions;
    }
}
