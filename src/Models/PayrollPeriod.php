<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'year',
        'month',
        'start_date',
        'end_date',
        'payment_date',
        'status',
        'employee_count',
        'total_gross',
        'total_deductions',
        'total_net',
        'created_by',
        'approved_by',
        'approved_at',
        'processed_by',
        'paid_by',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'payment_date' => 'date',
        'employee_count' => 'integer',
        'total_gross' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'total_net' => 'decimal:2',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'year' => 'integer',
        'month' => 'integer',
    ];

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_PROCESSED = 'processed';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_PAID = 'paid';

    public const STATUS_CANCELLED = 'cancelled';

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    /**
     * Get period label.
     */
    public function getPeriodLabelAttribute(): string
    {
        if ($this->year && $this->month) {
            return \Carbon\Carbon::create($this->year, $this->month)->format('F Y');
        }

        return $this->name;
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => __('Draft'),
            self::STATUS_PROCESSING => __('Processing'),
            self::STATUS_PROCESSED => __('Processed'),
            self::STATUS_APPROVED => __('Approved'),
            self::STATUS_PAID => __('Paid'),
            self::STATUS_CANCELLED => __('Cancelled'),
            default => $this->status,
        };
    }

    /**
     * Get status color.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'gray',
            self::STATUS_PROCESSING => 'info',
            self::STATUS_PROCESSED => 'warning',
            self::STATUS_APPROVED => 'success',
            self::STATUS_PAID => 'primary',
            self::STATUS_CANCELLED => 'danger',
            default => 'gray',
        };
    }

    /**
     * Check if can be processed.
     */
    public function canBeProcessed(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Check if can be approved.
     */
    public function canBeApproved(): bool
    {
        return $this->status === self::STATUS_PROCESSED;
    }

    /**
     * Check if can be paid.
     */
    public function canBePaid(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if can be edited.
     */
    public function canBeEdited(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PROCESSED]);
    }

    /**
     * Mark as processing.
     */
    public function startProcessing(): void
    {
        $this->update(['status' => self::STATUS_PROCESSING]);
    }

    /**
     * Mark as processed.
     */
    public function markProcessed(): void
    {
        $this->update([
            'status' => self::STATUS_PROCESSED,
        ]);

        $this->recalculateTotals();
    }

    /**
     * Approve payroll.
     */
    public function approve(int $userId): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }

    /**
     * Mark as paid.
     */
    public function markPaid(int $userId): void
    {
        $this->update([
            'status' => self::STATUS_PAID,
            'paid_by' => $userId,
            'paid_at' => now(),
        ]);

        $this->payslips()->update(['status' => Payslip::STATUS_PAID]);
    }

    /**
     * Recalculate totals from payslips.
     */
    public function recalculateTotals(): void
    {
        $totals = $this->payslips()
            ->selectRaw('COUNT(*) as count, SUM(gross_salary) as gross, SUM(total_deductions) as deductions, SUM(net_salary) as net')
            ->first();

        $this->update([
            'employee_count' => $totals->count ?? 0,
            'total_gross' => $totals->gross ?? 0,
            'total_deductions' => $totals->deductions ?? 0,
            'total_net' => $totals->net ?? 0,
        ]);
    }

    /**
     * Create period for month.
     */
    public static function createForMonth(int $year, int $month): self
    {
        $startDate = \Carbon\Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        return static::create([
            'name' => $startDate->format('F Y'),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'payment_date' => $endDate->copy()->addDays(5),
            'status' => self::STATUS_DRAFT,
        ]);
    }

    public function scopeForYear($query, int $year)
    {
        return $query->whereYear('start_date', $year);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
