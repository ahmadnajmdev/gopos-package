<?php

namespace Gopos\Models;

use Gopos\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyProgram extends Model
{
    use BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'name',
        'name_ar',
        'name_ckb',
        'type',
        'points_per_currency',
        'currency_per_point',
        'min_redemption_points',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'points_per_currency' => 'decimal:4',
        'currency_per_point' => 'decimal:4',
        'min_redemption_points' => 'integer',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    /**
     * Get all customer loyalty records for this program.
     */
    public function customerLoyalties(): HasMany
    {
        return $this->hasMany(CustomerLoyalty::class);
    }

    /**
     * Get localized name.
     */
    public function getLocalizedNameAttribute(): string
    {
        $locale = app()->getLocale();

        return match ($locale) {
            'ar' => $this->name_ar ?: $this->name,
            'ckb' => $this->name_ckb ?: $this->name_ar ?: $this->name,
            default => $this->name,
        };
    }

    /**
     * Calculate points for a given amount.
     */
    public function calculatePoints(float $amount): int
    {
        return (int) floor($amount * $this->points_per_currency);
    }

    /**
     * Calculate currency value for given points.
     */
    public function calculatePointsValue(int $points): float
    {
        return $points * $this->currency_per_point;
    }

    /**
     * Check if points can be redeemed.
     */
    public function canRedeem(int $points): bool
    {
        return $points >= $this->min_redemption_points;
    }

    /**
     * Get tier for given lifetime points.
     */
    public function getTierForPoints(int $lifetimePoints): ?string
    {
        $tiers = $this->settings['tiers'] ?? null;

        if (! $tiers) {
            return null;
        }

        // Tiers should be sorted descending by threshold
        $sortedTiers = collect($tiers)->sortByDesc('threshold');

        foreach ($sortedTiers as $tier) {
            if ($lifetimePoints >= $tier['threshold']) {
                return $tier['name'];
            }
        }

        return null;
    }

    /**
     * Scope for active programs.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get default loyalty program.
     */
    public static function getDefault(): ?self
    {
        return static::active()->first();
    }
}
