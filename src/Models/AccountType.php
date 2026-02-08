<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountType extends Model
{
    use HasFactory;

    // No BelongsToTeam - this is system-wide reference data

    protected $fillable = [
        'name',
        'name_ar',
        'name_ckb',
        'normal_balance',
        'display_order',
    ];

    /**
     * Get localized name based on current locale
     */
    public function getLocalizedNameAttribute(): string
    {
        $locale = app()->getLocale();

        if ($locale === 'ar' && ! empty($this->name_ar)) {
            return $this->name_ar;
        }

        if ($locale === 'ckb' && ! empty($this->name_ckb)) {
            return $this->name_ckb;
        }

        return $this->name;
    }

    /**
     * Check if this account type has a debit normal balance
     */
    public function isDebitBalance(): bool
    {
        return $this->normal_balance === 'debit';
    }

    /**
     * Check if this account type has a credit normal balance
     */
    public function isCreditBalance(): bool
    {
        return $this->normal_balance === 'credit';
    }

    // Relationships

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    // Static helpers for common account types

    public static function asset(): ?self
    {
        return static::where('name', 'Asset')->first();
    }

    public static function liability(): ?self
    {
        return static::where('name', 'Liability')->first();
    }

    public static function equity(): ?self
    {
        return static::where('name', 'Equity')->first();
    }

    public static function revenue(): ?self
    {
        return static::where('name', 'Revenue')->first();
    }

    public static function expense(): ?self
    {
        return static::where('name', 'Expense')->first();
    }
}
