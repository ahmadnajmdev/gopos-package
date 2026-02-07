<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ar',
        'code',
        'rate',
        'type',
        'applies_to',
        'is_active',
        'description',
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'is_active' => 'boolean',
        'applies_to' => 'array',
    ];

    /**
     * Scope to get active tax codes only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get tax codes applicable for sales
     */
    public function scopeForSales($query)
    {
        return $query->where(function ($q) {
            $q->whereJsonContains('applies_to', 'sales')
                ->orWhereJsonContains('applies_to', 'both');
        });
    }

    /**
     * Scope to get tax codes applicable for purchases
     */
    public function scopeForPurchases($query)
    {
        return $query->where(function ($q) {
            $q->whereJsonContains('applies_to', 'purchases')
                ->orWhereJsonContains('applies_to', 'both');
        });
    }

    /**
     * Calculate tax amount for a given amount
     */
    public function calculateTax(float $amount): array
    {
        if ($this->type === 'inclusive') {
            // Tax is included in the amount
            $taxAmount = $amount - ($amount / (1 + $this->rate));
            $netAmount = $amount - $taxAmount;
        } else {
            // Tax is exclusive (added on top)
            $taxAmount = $amount * $this->rate;
            $netAmount = $amount;
        }

        return [
            'net_amount' => round($netAmount, 2),
            'tax_amount' => round($taxAmount, 2),
            'gross_amount' => round($netAmount + $taxAmount, 2),
            'tax_rate' => $this->rate,
        ];
    }

    /**
     * Check if tax code is applicable to a specific type
     */
    public function isApplicableTo(string $type): bool
    {
        if (empty($this->applies_to)) {
            return true; // Default: applicable to all
        }

        return in_array($type, $this->applies_to) || in_array('both', $this->applies_to);
    }

    /**
     * Get rate as percentage for display
     */
    public function getRatePercentageAttribute(): float
    {
        return $this->rate * 100;
    }

    /**
     * Get formatted rate string
     */
    public function getFormattedRateAttribute(): string
    {
        return number_format($this->rate * 100, 2).'%';
    }

    /**
     * Get localized name based on current locale
     */
    public function getLocalizedNameAttribute(): string
    {
        $locale = app()->getLocale();

        if ($locale === 'ar' && ! empty($this->name_ar)) {
            return $this->name_ar;
        }

        return $this->name;
    }

    // Relationships

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'default_tax_code_id');
    }

    public function exemptions(): HasMany
    {
        return $this->hasMany(TaxExemption::class);
    }
}
