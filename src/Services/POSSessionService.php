<?php

namespace Gopos\Services;

use Carbon\Carbon;
use Gopos\Models\Currency;
use Gopos\Models\PosSession;
use Gopos\Models\PosSessionTransaction;
use Gopos\Models\Sale;
use Gopos\Models\User;
use Illuminate\Support\Collection;

class POSSessionService
{
    /**
     * Open a new POS session.
     */
    public function openSession(User $user, float $openingCash, ?string $terminalId = null): PosSession
    {
        // Close any existing open sessions for this user
        $this->closeAllOpenSessions($user);

        return PosSession::create([
            'user_id' => $user->id,
            'terminal_id' => $terminalId,
            'opening_time' => now(),
            'opening_cash' => $openingCash,
            'status' => 'open',
        ]);
    }

    /**
     * Close a POS session.
     */
    public function closeSession(PosSession $session, float $closingCash, ?User $closedBy = null, ?string $notes = null): PosSession
    {
        $expectedCash = $session->calculateExpectedCash();
        $difference = $closingCash - $expectedCash;

        $session->update([
            'closing_time' => now(),
            'closing_cash' => $closingCash,
            'expected_cash' => $expectedCash,
            'cash_difference' => $difference,
            'status' => 'closed',
            'closed_by' => $closedBy?->id ?? auth()->id(),
            'notes' => $notes,
        ]);

        return $session->fresh();
    }

    /**
     * Suspend a POS session (temporary pause).
     */
    public function suspendSession(PosSession $session): PosSession
    {
        $session->update(['status' => 'suspended']);

        return $session->fresh();
    }

    /**
     * Resume a suspended session.
     */
    public function resumeSession(PosSession $session): PosSession
    {
        if ($session->status !== 'suspended') {
            throw new \Exception(__('Only suspended sessions can be resumed.'));
        }

        $session->update(['status' => 'open']);

        return $session->fresh();
    }

    /**
     * Record a transaction in the session.
     */
    public function recordTransaction(
        PosSession $session,
        string $type,
        float $amount,
        string $paymentMethod = 'cash',
        ?Currency $currency = null,
        ?array $data = []
    ): PosSessionTransaction {
        $currency = $currency ?? Currency::getBaseCurrency();

        return PosSessionTransaction::create([
            'pos_session_id' => $session->id,
            'type' => $type,
            'reference_type' => $data['reference_type'] ?? null,
            'reference_id' => $data['reference_id'] ?? null,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'currency_id' => $currency->id,
            'exchange_rate' => $currency->exchange_rate ?? 1,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Record a sale transaction.
     */
    public function recordSaleTransaction(PosSession $session, Sale $sale, string $paymentMethod, float $amount): PosSessionTransaction
    {
        return $this->recordTransaction($session, 'sale', $amount, $paymentMethod, $sale->currency, [
            'reference_type' => Sale::class,
            'reference_id' => $sale->id,
        ]);
    }

    /**
     * Record a refund transaction.
     */
    public function recordRefundTransaction(PosSession $session, $refund, string $paymentMethod, float $amount): PosSessionTransaction
    {
        return $this->recordTransaction($session, 'refund', $amount, $paymentMethod, null, [
            'reference_type' => get_class($refund),
            'reference_id' => $refund->id,
        ]);
    }

    /**
     * Record a cash in transaction.
     */
    public function recordCashIn(PosSession $session, float $amount, ?string $notes = null): PosSessionTransaction
    {
        return $this->recordTransaction($session, 'cash_in', $amount, 'cash', null, [
            'notes' => $notes,
        ]);
    }

    /**
     * Record a cash out transaction.
     */
    public function recordCashOut(PosSession $session, float $amount, ?string $notes = null): PosSessionTransaction
    {
        return $this->recordTransaction($session, 'cash_out', $amount, 'cash', null, [
            'notes' => $notes,
        ]);
    }

    /**
     * Get session summary/report.
     */
    public function getSessionSummary(PosSession $session): array
    {
        $transactions = $session->transactions;

        // Group by payment method
        $byPaymentMethod = $transactions->groupBy('payment_method');

        $summary = [
            'session' => $session,
            'opening_cash' => $session->opening_cash,
            'closing_cash' => $session->closing_cash,
            'expected_cash' => $session->calculateExpectedCash(),
            'cash_difference' => $session->cash_difference,
            'sales_count' => $session->sales_count,
            'total_sales' => $session->total_sales_amount,
            'by_payment_method' => [],
            'cash_movements' => [
                'sales' => $transactions->where('type', 'sale')->where('payment_method', 'cash')->sum('amount'),
                'refunds' => $transactions->where('type', 'refund')->where('payment_method', 'cash')->sum('amount'),
                'cash_in' => $transactions->where('type', 'cash_in')->sum('amount'),
                'cash_out' => $transactions->whereIn('type', ['cash_out', 'expense'])->sum('amount'),
            ],
            'transactions' => $transactions,
        ];

        foreach ($byPaymentMethod as $method => $methodTransactions) {
            $sales = $methodTransactions->where('type', 'sale')->sum('amount');
            $refunds = $methodTransactions->where('type', 'refund')->sum('amount');

            $summary['by_payment_method'][$method] = [
                'sales' => $sales,
                'refunds' => $refunds,
                'net' => $sales - $refunds,
                'count' => $methodTransactions->where('type', 'sale')->count(),
            ];
        }

        return $summary;
    }

    /**
     * Get current open session for user.
     */
    public function getCurrentSession(?User $user = null): ?PosSession
    {
        $user = $user ?? auth()->user();

        if (! $user) {
            return null;
        }

        return PosSession::where('user_id', $user->id)
            ->where('status', 'open')
            ->latest('opening_time')
            ->first();
    }

    /**
     * Check if user has an open session.
     */
    public function hasOpenSession(?User $user = null): bool
    {
        return $this->getCurrentSession($user) !== null;
    }

    /**
     * Close all open sessions for a user.
     */
    public function closeAllOpenSessions(User $user): int
    {
        return PosSession::where('user_id', $user->id)
            ->where('status', 'open')
            ->update([
                'status' => 'closed',
                'closing_time' => now(),
                'notes' => __('Auto-closed when opening new session'),
            ]);
    }

    /**
     * Get daily summary for a specific date.
     */
    public function getDailySummary(Carbon $date): array
    {
        $sessions = PosSession::whereDate('opening_time', $date)->get();

        $totals = [
            'date' => $date->toDateString(),
            'sessions_count' => $sessions->count(),
            'total_opening_cash' => $sessions->sum('opening_cash'),
            'total_closing_cash' => $sessions->sum('closing_cash'),
            'total_cash_difference' => $sessions->sum('cash_difference'),
            'total_sales' => 0,
            'total_refunds' => 0,
            'by_payment_method' => [],
        ];

        foreach ($sessions as $session) {
            $summary = $this->getSessionSummary($session);
            $totals['total_sales'] += $summary['total_sales'];

            foreach ($summary['by_payment_method'] as $method => $data) {
                if (! isset($totals['by_payment_method'][$method])) {
                    $totals['by_payment_method'][$method] = [
                        'sales' => 0,
                        'refunds' => 0,
                        'net' => 0,
                        'count' => 0,
                    ];
                }

                $totals['by_payment_method'][$method]['sales'] += $data['sales'];
                $totals['by_payment_method'][$method]['refunds'] += $data['refunds'];
                $totals['by_payment_method'][$method]['net'] += $data['net'];
                $totals['by_payment_method'][$method]['count'] += $data['count'];
            }
        }

        return $totals;
    }

    /**
     * Get sessions for a date range.
     */
    public function getSessionsForPeriod(Carbon $startDate, Carbon $endDate): Collection
    {
        return PosSession::whereBetween('opening_time', [$startDate, $endDate])
            ->with(['user', 'transactions'])
            ->orderBy('opening_time', 'desc')
            ->get();
    }
}
