<?php

namespace Gopos\Models;

use Gopos\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Budget extends Model
{
    use Auditable;

    protected $fillable = [
        'name',
        'name_ar',
        'name_ckb',
        'description',
        'fiscal_period_id',
        'budget_type',
        'status',
        'total_amount',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public const TYPE_OPERATING = 'operating';

    public const TYPE_CAPITAL = 'capital';

    public const TYPE_CASH_FLOW = 'cash_flow';

    public const TYPE_PROJECT = 'project';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_CLOSED = 'closed';

    public function fiscalPeriod(): BelongsTo
    {
        return $this->belongsTo(FiscalPeriod::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(BudgetLine::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(BudgetRevision::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get localized name.
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
     * Calculate total budget.
     */
    public function calculateTotal(): float
    {
        return $this->lines()->sum('annual_total');
    }

    /**
     * Update total amount.
     */
    public function updateTotal(): void
    {
        $this->update(['total_amount' => $this->calculateTotal()]);
    }

    /**
     * Get actual spending for a month.
     */
    public function getActualForMonth(int $month): float
    {
        $startDate = $this->fiscalPeriod->start_date->copy()->month($month)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $total = 0;
        foreach ($this->lines as $line) {
            $total += $line->getActualForPeriod($startDate, $endDate);
        }

        return $total;
    }

    /**
     * Get budget vs actual comparison.
     */
    public function getVarianceAnalysis(): array
    {
        $analysis = [];
        $months = ['january', 'february', 'march', 'april', 'may', 'june',
            'july', 'august', 'september', 'october', 'november', 'december'];

        foreach ($months as $index => $month) {
            $budgeted = $this->lines()->sum($month);
            $actual = $this->getActualForMonth($index + 1);
            $variance = $budgeted - $actual;
            $variancePercent = $budgeted != 0 ? ($variance / $budgeted) * 100 : 0;

            $analysis[$month] = [
                'budgeted' => $budgeted,
                'actual' => $actual,
                'variance' => $variance,
                'variance_percent' => $variancePercent,
                'status' => $variance >= 0 ? 'under' : 'over',
            ];
        }

        return $analysis;
    }

    /**
     * Approve the budget.
     */
    public function approve(): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
    }

    /**
     * Activate the budget.
     */
    public function activate(): void
    {
        $this->update(['status' => self::STATUS_ACTIVE]);
    }

    /**
     * Close the budget.
     */
    public function close(): void
    {
        $this->update(['status' => self::STATUS_CLOSED]);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('budget_type', $type);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->created_by)) {
                $model->created_by = auth()->id();
            }
        });
    }
}
