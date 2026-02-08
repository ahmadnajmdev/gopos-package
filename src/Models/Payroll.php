<?php

namespace Gopos\Models;

use Gopos\Enums\PayrollStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'pay_period_start',
        'pay_period_end',
        'basic_salary',
        'deductions',
        'bonuses',
        'overtime_pay',
        'net_pay',
        'status',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'pay_period_start' => 'date',
            'pay_period_end' => 'date',
            'basic_salary' => 'decimal:2',
            'deductions' => 'decimal:2',
            'bonuses' => 'decimal:2',
            'overtime_pay' => 'decimal:2',
            'net_pay' => 'decimal:2',
            'status' => PayrollStatus::class,
            'paid_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Payroll $payroll) {
            $payroll->net_pay = $payroll->basic_salary + $payroll->bonuses + $payroll->overtime_pay - $payroll->deductions;
        });

        static::updating(function (Payroll $payroll) {
            $payroll->net_pay = $payroll->basic_salary + $payroll->bonuses + $payroll->overtime_pay - $payroll->deductions;
        });
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
