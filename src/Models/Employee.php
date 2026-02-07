<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'department_id',
        'position_id',
        'manager_id',
        'work_schedule_id',
        'employee_number',
        'first_name',
        'first_name_ar',
        'first_name_ckb',
        'last_name',
        'last_name_ar',
        'last_name_ckb',
        'email',
        'phone',
        'mobile',
        'birth_date',
        'gender',
        'marital_status',
        'national_id',
        'passport_number',
        'nationality',
        'address',
        'city',
        'state',
        'country',
        'employment_type',
        'status',
        'hire_date',
        'probation_end_date',
        'contract_end_date',
        'termination_date',
        'termination_reason',
        'basic_salary',
        'salary_type',
        'currency_id',
        'bank_name',
        'bank_account_number',
        'bank_iban',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',
        'photo',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'hire_date' => 'date',
        'probation_end_date' => 'date',
        'contract_end_date' => 'date',
        'termination_date' => 'date',
        'basic_salary' => 'decimal:2',
    ];

    public const STATUS_ACTIVE = 'active';

    public const STATUS_ON_LEAVE = 'on_leave';

    public const STATUS_SUSPENDED = 'suspended';

    public const STATUS_TERMINATED = 'terminated';

    public const STATUS_RESIGNED = 'resigned';

    public const TYPE_FULL_TIME = 'full_time';

    public const TYPE_PART_TIME = 'part_time';

    public const TYPE_CONTRACT = 'contract';

    public const TYPE_TEMPORARY = 'temporary';

    public const TYPE_INTERN = 'intern';

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    public function workSchedule(): BelongsTo
    {
        return $this->belongsTo(WorkSchedule::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function qualifications(): HasMany
    {
        return $this->hasMany(EmployeeQualification::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    public function payrollComponents(): HasMany
    {
        return $this->hasMany(EmployeePayrollComponent::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(EmployeeLoan::class);
    }

    public function overtimeRequests(): HasMany
    {
        return $this->hasMany(OvertimeRequest::class);
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return $this->first_name.' '.$this->last_name;
    }

    public function getLocalizedFullNameAttribute(): string
    {
        $locale = app()->getLocale();

        if ($locale === 'ar') {
            $first = $this->first_name_ar ?: $this->first_name;
            $last = $this->last_name_ar ?: $this->last_name;
        } elseif ($locale === 'ckb') {
            $first = $this->first_name_ckb ?: $this->first_name;
            $last = $this->last_name_ckb ?: $this->last_name;
        } else {
            $first = $this->first_name;
            $last = $this->last_name;
        }

        return $first.' '.$last;
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->employee_number.' - '.$this->localized_full_name;
    }

    public function getAgeAttribute(): ?int
    {
        return $this->birth_date?->age;
    }

    public function getServiceYearsAttribute(): float
    {
        return round($this->hire_date->diffInYears(now()), 1);
    }

    public function getServiceDaysAttribute(): int
    {
        return $this->hire_date->diffInDays(now());
    }

    public function getIsOnProbationAttribute(): bool
    {
        if (! $this->probation_end_date) {
            return false;
        }

        return now()->lt($this->probation_end_date);
    }

    public function getIsContractExpiringAttribute(): bool
    {
        if (! $this->contract_end_date) {
            return false;
        }

        return now()->diffInDays($this->contract_end_date) <= 30;
    }

    // Methods
    public static function generateEmployeeNumber(): string
    {
        $lastNumber = static::withTrashed()
            ->selectRaw('MAX(CAST(SUBSTRING(employee_number, 5) AS UNSIGNED)) as max_num')
            ->value('max_num');
        $nextNumber = ($lastNumber ?? 0) + 1;

        return 'EMP-'.str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    public function getLeaveBalance(int $leaveTypeId, ?int $year = null): ?LeaveBalance
    {
        $year = $year ?? now()->year;

        return $this->leaveBalances()
            ->where('leave_type_id', $leaveTypeId)
            ->where('year', $year)
            ->first();
    }

    public function getAvailableLeave(int $leaveTypeId, ?int $year = null): float
    {
        $balance = $this->getLeaveBalance($leaveTypeId, $year);

        if (! $balance) {
            return 0;
        }

        return $balance->entitled_days + $balance->carried_forward + $balance->adjustment
               - $balance->used_days - $balance->pending_days;
    }

    public function terminate(string $reason, ?string $date = null): void
    {
        $this->update([
            'status' => self::STATUS_TERMINATED,
            'termination_date' => $date ?? now(),
            'termination_reason' => $reason,
        ]);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByEmploymentType($query, string $type)
    {
        return $query->where('employment_type', $type);
    }

    public function scopeOnProbation($query)
    {
        return $query->whereNotNull('probation_end_date')
            ->where('probation_end_date', '>', now());
    }

    public function scopeContractExpiring($query, int $days = 30)
    {
        return $query->whereNotNull('contract_end_date')
            ->whereBetween('contract_end_date', [now(), now()->addDays($days)]);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->employee_number)) {
                $model->employee_number = static::generateEmployeeNumber();
            }
        });
    }
}
