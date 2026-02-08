<?php

namespace Gopos\Models;

use Gopos\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerLoyalty extends Model
{
    use BelongsToBranch;

    protected $table = 'customer_loyalty';

    protected $fillable = [
        'branch_id',
        'customer_id',
        'loyalty_program_id',
        'points_balance',
        'lifetime_points',
        'tier',
        'tier_updated_at',
    ];

    protected $casts = [
        'points_balance' => 'integer',
        'lifetime_points' => 'integer',
        'tier_updated_at' => 'datetime',
    ];

    /**
     * Get the customer.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the loyalty program.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(LoyaltyProgram::class, 'loyalty_program_id');
    }

    /**
     * Get all transactions for this loyalty record.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    /**
     * Add points to balance.
     */
    public function addPoints(int $points, ?Sale $sale = null, ?string $description = null): LoyaltyTransaction
    {
        $this->increment('points_balance', $points);
        $this->increment('lifetime_points', $points);

        $transaction = $this->transactions()->create([
            'type' => 'earn',
            'points' => $points,
            'sale_id' => $sale?->id,
            'description' => $description ?? __('Points earned from sale'),
        ]);

        $this->updateTier();

        return $transaction;
    }

    /**
     * Redeem points from balance.
     */
    public function redeemPoints(int $points, ?Sale $sale = null, ?string $description = null): ?LoyaltyTransaction
    {
        if ($points > $this->points_balance) {
            return null;
        }

        if (! $this->program->canRedeem($points)) {
            return null;
        }

        $this->decrement('points_balance', $points);

        return $this->transactions()->create([
            'type' => 'redeem',
            'points' => -$points,
            'sale_id' => $sale?->id,
            'description' => $description ?? __('Points redeemed'),
        ]);
    }

    /**
     * Adjust points (admin correction).
     */
    public function adjustPoints(int $points, string $description): LoyaltyTransaction
    {
        if ($points > 0) {
            $this->increment('points_balance', $points);
            $this->increment('lifetime_points', $points);
        } else {
            $this->decrement('points_balance', abs($points));
        }

        return $this->transactions()->create([
            'type' => 'adjust',
            'points' => $points,
            'description' => $description,
        ]);
    }

    /**
     * Add bonus points.
     */
    public function addBonus(int $points, string $description): LoyaltyTransaction
    {
        $this->increment('points_balance', $points);
        $this->increment('lifetime_points', $points);

        $transaction = $this->transactions()->create([
            'type' => 'bonus',
            'points' => $points,
            'description' => $description,
        ]);

        $this->updateTier();

        return $transaction;
    }

    /**
     * Update customer tier based on lifetime points.
     */
    public function updateTier(): void
    {
        $newTier = $this->program->getTierForPoints($this->lifetime_points);

        if ($newTier !== $this->tier) {
            $this->update([
                'tier' => $newTier,
                'tier_updated_at' => now(),
            ]);
        }
    }

    /**
     * Get points value in currency.
     */
    public function getPointsValueAttribute(): float
    {
        return $this->program->calculatePointsValue($this->points_balance);
    }

    /**
     * Check if customer can redeem points.
     */
    public function canRedeem(?int $points = null): bool
    {
        $pointsToCheck = $points ?? $this->points_balance;

        return $pointsToCheck >= $this->program->min_redemption_points
            && $pointsToCheck <= $this->points_balance;
    }

    /**
     * Get or create loyalty record for customer.
     */
    public static function getOrCreateForCustomer(Customer $customer, ?LoyaltyProgram $program = null): ?self
    {
        $program = $program ?? LoyaltyProgram::getDefault();

        if (! $program) {
            return null;
        }

        return static::firstOrCreate([
            'customer_id' => $customer->id,
            'loyalty_program_id' => $program->id,
        ]);
    }
}
