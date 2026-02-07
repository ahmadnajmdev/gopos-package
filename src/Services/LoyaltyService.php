<?php

namespace Gopos\Services;

use Gopos\Models\Customer;
use Gopos\Models\CustomerLoyalty;
use Gopos\Models\LoyaltyProgram;
use Gopos\Models\LoyaltyTransaction;
use Gopos\Models\Sale;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LoyaltyService
{
    /**
     * Enroll customer in loyalty program.
     */
    public function enrollCustomer(Customer $customer, ?LoyaltyProgram $program = null): ?CustomerLoyalty
    {
        $program = $program ?? LoyaltyProgram::getDefault();

        if (! $program) {
            return null;
        }

        return CustomerLoyalty::firstOrCreate([
            'customer_id' => $customer->id,
            'loyalty_program_id' => $program->id,
        ], [
            'points_balance' => 0,
            'lifetime_points' => 0,
            'tier' => null,
        ]);
    }

    /**
     * Calculate points for a sale.
     */
    public function calculatePointsForSale(Sale $sale): int
    {
        $customer = $sale->customer;

        if (! $customer) {
            return 0;
        }

        $loyalty = $this->getCustomerLoyalty($customer);

        if (! $loyalty || ! $loyalty->program) {
            return 0;
        }

        // Use base currency amount for consistent point calculation
        $amount = $sale->amount_in_base_currency ?? $sale->total_amount;

        return $loyalty->program->calculatePoints($amount);
    }

    /**
     * Award points to customer for a sale.
     */
    public function awardPointsForSale(Sale $sale): ?LoyaltyTransaction
    {
        $customer = $sale->customer;

        if (! $customer) {
            return null;
        }

        $loyalty = $this->getOrEnrollCustomer($customer);

        if (! $loyalty) {
            return null;
        }

        $points = $this->calculatePointsForSale($sale);

        if ($points <= 0) {
            return null;
        }

        return $loyalty->addPoints($points, $sale, __('Points earned from sale #:number', [
            'number' => $sale->sale_number,
        ]));
    }

    /**
     * Redeem points for a sale.
     */
    public function redeemPoints(
        Customer $customer,
        int $points,
        ?Sale $sale = null,
        ?string $description = null
    ): ?LoyaltyTransaction {
        $loyalty = $this->getCustomerLoyalty($customer);

        if (! $loyalty) {
            return null;
        }

        return $loyalty->redeemPoints(
            $points,
            $sale,
            $description ?? __('Points redeemed')
        );
    }

    /**
     * Calculate redemption value for points.
     */
    public function calculateRedemptionValue(Customer $customer, int $points): float
    {
        $loyalty = $this->getCustomerLoyalty($customer);

        if (! $loyalty || ! $loyalty->program) {
            return 0;
        }

        return $loyalty->program->calculatePointsValue($points);
    }

    /**
     * Get maximum points customer can redeem.
     */
    public function getMaxRedeemablePoints(Customer $customer): int
    {
        $loyalty = $this->getCustomerLoyalty($customer);

        if (! $loyalty) {
            return 0;
        }

        return $loyalty->points_balance;
    }

    /**
     * Check if customer can redeem points.
     */
    public function canRedeem(Customer $customer, int $points): bool
    {
        $loyalty = $this->getCustomerLoyalty($customer);

        if (! $loyalty) {
            return false;
        }

        return $loyalty->canRedeem($points);
    }

    /**
     * Get customer loyalty info.
     */
    public function getCustomerLoyalty(Customer $customer): ?CustomerLoyalty
    {
        return CustomerLoyalty::where('customer_id', $customer->id)
            ->with('program')
            ->first();
    }

    /**
     * Get or enroll customer in default program.
     */
    public function getOrEnrollCustomer(Customer $customer): ?CustomerLoyalty
    {
        $loyalty = $this->getCustomerLoyalty($customer);

        if (! $loyalty) {
            $loyalty = $this->enrollCustomer($customer);
        }

        return $loyalty;
    }

    /**
     * Add bonus points to customer.
     */
    public function addBonusPoints(
        Customer $customer,
        int $points,
        string $description
    ): ?LoyaltyTransaction {
        $loyalty = $this->getOrEnrollCustomer($customer);

        if (! $loyalty) {
            return null;
        }

        return $loyalty->addBonus($points, $description);
    }

    /**
     * Adjust customer points.
     */
    public function adjustPoints(
        Customer $customer,
        int $points,
        string $description
    ): ?LoyaltyTransaction {
        $loyalty = $this->getOrEnrollCustomer($customer);

        if (! $loyalty) {
            return null;
        }

        return $loyalty->adjustPoints($points, $description);
    }

    /**
     * Get customer loyalty summary.
     */
    public function getCustomerSummary(Customer $customer): array
    {
        $loyalty = $this->getCustomerLoyalty($customer);

        if (! $loyalty) {
            return [
                'enrolled' => false,
                'points_balance' => 0,
                'lifetime_points' => 0,
                'tier' => null,
                'points_value' => 0,
                'can_redeem' => false,
                'min_redemption_points' => null,
            ];
        }

        return [
            'enrolled' => true,
            'points_balance' => $loyalty->points_balance,
            'lifetime_points' => $loyalty->lifetime_points,
            'tier' => $loyalty->tier,
            'points_value' => $loyalty->pointsValue,
            'can_redeem' => $loyalty->canRedeem(),
            'min_redemption_points' => $loyalty->program?->min_redemption_points,
            'program_name' => $loyalty->program?->localizedName,
        ];
    }

    /**
     * Get transaction history for customer.
     */
    public function getTransactionHistory(Customer $customer, int $limit = 20): Collection
    {
        $loyalty = $this->getCustomerLoyalty($customer);

        if (! $loyalty) {
            return collect();
        }

        return $loyalty->transactions()
            ->with('sale')
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Expire old points.
     */
    public function expirePoints(int $daysOld = 365): int
    {
        $expiredCount = 0;
        $cutoffDate = now()->subDays($daysOld);

        // Get loyalty records with points to expire
        $loyalties = CustomerLoyalty::where('points_balance', '>', 0)->get();

        foreach ($loyalties as $loyalty) {
            // Check last earning transaction
            $lastEarning = $loyalty->transactions()
                ->where('type', 'earn')
                ->latest('created_at')
                ->first();

            if ($lastEarning && $lastEarning->created_at->lt($cutoffDate)) {
                // Expire all points
                $pointsToExpire = $loyalty->points_balance;

                if ($pointsToExpire > 0) {
                    $loyalty->transactions()->create([
                        'type' => 'expire',
                        'points' => -$pointsToExpire,
                        'description' => __('Points expired after :days days of inactivity', [
                            'days' => $daysOld,
                        ]),
                    ]);

                    $loyalty->update(['points_balance' => 0]);
                    $expiredCount++;
                }
            }
        }

        return $expiredCount;
    }

    /**
     * Get active loyalty programs.
     */
    public function getActivePrograms(): Collection
    {
        return LoyaltyProgram::active()->get();
    }

    /**
     * Calculate tier progress.
     */
    public function getTierProgress(Customer $customer): array
    {
        $loyalty = $this->getCustomerLoyalty($customer);

        if (! $loyalty || ! $loyalty->program) {
            return [
                'current_tier' => null,
                'next_tier' => null,
                'points_to_next_tier' => null,
                'progress_percentage' => 0,
            ];
        }

        $tiers = $loyalty->program->settings['tiers'] ?? null;

        if (! $tiers) {
            return [
                'current_tier' => null,
                'next_tier' => null,
                'points_to_next_tier' => null,
                'progress_percentage' => 0,
            ];
        }

        // Sort tiers by threshold
        $sortedTiers = collect($tiers)->sortBy('threshold')->values();

        $currentTier = null;
        $nextTier = null;
        $currentThreshold = 0;

        foreach ($sortedTiers as $index => $tier) {
            if ($loyalty->lifetime_points >= $tier['threshold']) {
                $currentTier = $tier;
                $currentThreshold = $tier['threshold'];

                if (isset($sortedTiers[$index + 1])) {
                    $nextTier = $sortedTiers[$index + 1];
                }
            } else {
                if (! $currentTier) {
                    $nextTier = $tier;
                }
                break;
            }
        }

        $pointsToNext = $nextTier ? max(0, $nextTier['threshold'] - $loyalty->lifetime_points) : 0;
        $progressPercentage = 0;

        if ($nextTier && $currentTier) {
            $range = $nextTier['threshold'] - $currentTier['threshold'];
            $progress = $loyalty->lifetime_points - $currentTier['threshold'];
            $progressPercentage = $range > 0 ? min(100, ($progress / $range) * 100) : 100;
        } elseif ($nextTier) {
            $progressPercentage = min(100, ($loyalty->lifetime_points / $nextTier['threshold']) * 100);
        } elseif ($currentTier) {
            $progressPercentage = 100;
        }

        return [
            'current_tier' => $currentTier['name'] ?? null,
            'next_tier' => $nextTier['name'] ?? null,
            'points_to_next_tier' => $pointsToNext,
            'progress_percentage' => round($progressPercentage, 1),
        ];
    }

    /**
     * Process loyalty for completed sale.
     */
    public function processSale(Sale $sale): ?LoyaltyTransaction
    {
        if (! $sale->customer) {
            return null;
        }

        return DB::transaction(function () use ($sale) {
            return $this->awardPointsForSale($sale);
        });
    }

    /**
     * Reverse loyalty points for refunded sale.
     */
    public function reverseSalePoints(Sale $sale): ?LoyaltyTransaction
    {
        if (! $sale->customer) {
            return null;
        }

        $loyalty = $this->getCustomerLoyalty($sale->customer);

        if (! $loyalty) {
            return null;
        }

        // Find the earning transaction for this sale
        $earningTransaction = $loyalty->transactions()
            ->where('sale_id', $sale->id)
            ->where('type', 'earn')
            ->first();

        if (! $earningTransaction) {
            return null;
        }

        $pointsToReverse = abs($earningTransaction->points);

        return $loyalty->adjustPoints(
            -$pointsToReverse,
            __('Points reversed for refunded sale #:number', [
                'number' => $sale->sale_number,
            ])
        );
    }
}
