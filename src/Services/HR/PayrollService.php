<?php

namespace Gopos\Services\HR;

use Gopos\Models\Attendance;
use Gopos\Models\Employee;
use Gopos\Models\EmployeeLoan;
use Gopos\Models\EmployeePayrollComponent;
use Gopos\Models\OvertimeRequest;
use Gopos\Models\PayrollComponent;
use Gopos\Models\PayrollPeriod;
use Gopos\Models\Payslip;
use Gopos\Models\PayslipItem;
use Gopos\Services\GeneralLedgerService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    protected AttendanceService $attendanceService;

    protected ?GeneralLedgerService $glService;

    public function __construct(AttendanceService $attendanceService, ?GeneralLedgerService $glService = null)
    {
        $this->attendanceService = $attendanceService;
        $this->glService = $glService;
    }

    /**
     * Create payroll period.
     */
    public function createPeriod(int $year, int $month): PayrollPeriod
    {
        // Check if period already exists
        $exists = PayrollPeriod::where('year', $year)->where('month', $month)->exists();
        if ($exists) {
            throw new \Exception(__('Payroll period for :month/:year already exists', [
                'month' => $month,
                'year' => $year,
            ]));
        }

        return PayrollPeriod::createForMonth($year, $month);
    }

    /**
     * Process payroll for period.
     */
    public function processPayroll(PayrollPeriod $period, ?Collection $employees = null, int $processedBy = 0): void
    {
        if (! $period->canBeProcessed()) {
            throw new \Exception(__('Payroll period cannot be processed'));
        }

        $period->startProcessing();

        try {
            DB::beginTransaction();

            $employees = $employees ?? Employee::active()->get();

            foreach ($employees as $employee) {
                $this->processEmployeePayroll($period, $employee);
            }

            $period->markProcessed($processedBy);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $period->update(['status' => PayrollPeriod::STATUS_DRAFT]);
            throw $e;
        }
    }

    /**
     * Process payroll for single employee.
     */
    public function processEmployeePayroll(PayrollPeriod $period, Employee $employee): Payslip
    {
        // Get attendance summary
        $attendance = $this->attendanceService->getEmployeeSummary(
            $employee,
            $period->year,
            $period->month
        );

        // Calculate working days in period
        $workingDays = $this->attendanceService->calculateWorkingDays(
            $period->start_date,
            $period->end_date,
            $employee->workSchedule
        );

        // Get approved overtime
        $overtime = OvertimeRequest::approved()
            ->forPeriod($period->start_date, $period->end_date)
            ->where('employee_id', $employee->id)
            ->get();

        $overtimeHours = $overtime->sum('hours');
        $overtimeAmount = $overtime->sum('amount');

        // Calculate absent deduction
        $absentDays = $attendance['absent_days'];
        $dailyRate = $employee->basic_salary / $workingDays;
        $absentDeduction = $absentDays * $dailyRate;

        // Get active loans for deduction
        $activeLoan = EmployeeLoan::active()->forEmployee($employee->id)->first();
        $loanDeduction = $activeLoan ? $activeLoan->installment_amount : 0;

        // Create payslip
        $payslip = Payslip::create([
            'payroll_period_id' => $period->id,
            'employee_id' => $employee->id,
            'payslip_number' => Payslip::generateNumber($period),
            'basic_salary' => $employee->basic_salary,
            'total_earnings' => 0,
            'total_deductions' => 0,
            'gross_salary' => $employee->basic_salary,
            'net_salary' => 0,
            'working_days' => $workingDays,
            'days_worked' => $attendance['present_days'],
            'overtime_hours' => $overtimeHours,
            'overtime_amount' => $overtimeAmount,
            'leave_days' => $attendance['leave_days'],
            'absent_days' => $absentDays,
            'absent_deduction' => $absentDeduction,
            'loan_deduction' => $loanDeduction,
            'status' => Payslip::STATUS_PROCESSED,
        ]);

        // Add payroll components
        $this->addPayslipComponents($payslip, $employee);

        // Recalculate totals
        $payslip->recalculateTotals();

        // Record loan repayment if applicable
        if ($activeLoan && $loanDeduction > 0) {
            $activeLoan->recordRepayment($loanDeduction, $payslip->id);
        }

        return $payslip;
    }

    /**
     * Add payroll components to payslip.
     */
    protected function addPayslipComponents(Payslip $payslip, Employee $employee): void
    {
        $baseSalary = $employee->basic_salary;

        // Get mandatory components
        $mandatoryComponents = PayrollComponent::active()
            ->mandatory()
            ->where('applies_to_all', true)
            ->ordered()
            ->get();

        foreach ($mandatoryComponents as $component) {
            $amount = $component->calculateAmount($baseSalary);
            $this->createPayslipItem($payslip, $component, $amount);
        }

        // Get employee-specific components
        $employeeComponents = EmployeePayrollComponent::where('employee_id', $employee->id)
            ->effective($payslip->payrollPeriod->end_date->toDateString())
            ->with('payrollComponent')
            ->get();

        foreach ($employeeComponents as $empComponent) {
            $amount = $empComponent->calculateAmount($baseSalary);
            $this->createPayslipItem($payslip, $empComponent->payrollComponent, $amount);
        }
    }

    /**
     * Create payslip item.
     */
    protected function createPayslipItem(Payslip $payslip, PayrollComponent $component, float $amount): PayslipItem
    {
        return PayslipItem::create([
            'payslip_id' => $payslip->id,
            'payroll_component_id' => $component->id,
            'description' => $component->localized_name,
            'amount' => $amount,
            'is_manual' => false,
        ]);
    }

    /**
     * Add manual payslip item.
     */
    public function addManualItem(Payslip $payslip, int $componentId, float $amount, ?string $description = null): PayslipItem
    {
        $component = PayrollComponent::findOrFail($componentId);

        $item = PayslipItem::create([
            'payslip_id' => $payslip->id,
            'payroll_component_id' => $componentId,
            'description' => $description ?? $component->localized_name,
            'amount' => $amount,
            'is_manual' => true,
        ]);

        $payslip->recalculateTotals();

        return $item;
    }

    /**
     * Remove payslip item.
     */
    public function removeItem(PayslipItem $item): void
    {
        $payslip = $item->payslip;
        $item->delete();
        $payslip->recalculateTotals();
    }

    /**
     * Approve payroll.
     */
    public function approvePayroll(PayrollPeriod $period, int $approverId): void
    {
        if (! $period->canBeApproved()) {
            throw new \Exception(__('Payroll cannot be approved'));
        }

        $period->approve($approverId);
    }

    /**
     * Mark payroll as paid and create GL entries.
     */
    public function markAsPaid(PayrollPeriod $period, int $paidBy): void
    {
        if (! $period->canBePaid()) {
            throw new \Exception(__('Payroll cannot be marked as paid'));
        }

        DB::beginTransaction();

        try {
            $period->markPaid($paidBy);

            // Create GL entries if service is available
            if ($this->glService) {
                $this->createGLEntries($period);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Create GL entries for payroll.
     */
    protected function createGLEntries(PayrollPeriod $period): void
    {
        // This would integrate with the accounting module
        // Create journal entries for:
        // - Salary expense (debit)
        // - Deductions payable (credit)
        // - Cash/Bank (credit)

        $payslips = $period->payslips()->with(['items.payrollComponent.account'])->get();

        // Group by account
        $accountTotals = [];

        foreach ($payslips as $payslip) {
            // Basic salary to salary expense
            $accountTotals['salary_expense'] = ($accountTotals['salary_expense'] ?? 0) + $payslip->basic_salary;

            // Earnings to their accounts
            foreach ($payslip->items as $item) {
                $account = $item->payrollComponent->account;
                if ($account) {
                    $key = $item->payrollComponent->isEarning() ? 'expense' : 'liability';
                    $accountTotals[$key.'_'.$account->id] = ($accountTotals[$key.'_'.$account->id] ?? 0) + $item->amount;
                }
            }

            // Net salary to cash/bank
            $accountTotals['cash_bank'] = ($accountTotals['cash_bank'] ?? 0) + $payslip->net_salary;
        }

        // Create journal entry through GL service
        // This would need to be implemented based on accounting module structure
    }

    /**
     * Get payroll summary.
     */
    public function getPayrollSummary(PayrollPeriod $period): array
    {
        $payslips = $period->payslips()->with('employee.department')->get();

        // Group by department
        $byDepartment = $payslips->groupBy('employee.department_id');

        $departmentSummary = [];
        foreach ($byDepartment as $deptId => $deptPayslips) {
            $departmentSummary[] = [
                'department' => $deptPayslips->first()->employee->department?->localized_name ?? __('Unassigned'),
                'employees' => $deptPayslips->count(),
                'gross' => $deptPayslips->sum('gross_salary'),
                'deductions' => $deptPayslips->sum('total_deductions'),
                'net' => $deptPayslips->sum('net_salary'),
            ];
        }

        // Component breakdown
        $componentBreakdown = PayslipItem::whereIn('payslip_id', $payslips->pluck('id'))
            ->selectRaw('payroll_component_id, SUM(amount) as total')
            ->groupBy('payroll_component_id')
            ->with('payrollComponent')
            ->get()
            ->map(function ($item) {
                return [
                    'component' => $item->payrollComponent->localized_name,
                    'type' => $item->payrollComponent->type,
                    'total' => $item->total,
                ];
            });

        return [
            'period' => $period->period_label,
            'status' => $period->status_label,
            'total_employees' => $period->total_employees,
            'total_gross' => $period->total_gross,
            'total_deductions' => $period->total_deductions,
            'total_net' => $period->total_net,
            'by_department' => $departmentSummary,
            'components' => $componentBreakdown,
        ];
    }

    /**
     * Generate bank file for payments.
     */
    public function generateBankFile(PayrollPeriod $period, string $format = 'csv'): string
    {
        $payslips = $period->payslips()
            ->where('status', Payslip::STATUS_APPROVED)
            ->with('employee')
            ->get();

        $lines = [];
        foreach ($payslips as $payslip) {
            $employee = $payslip->employee;
            $lines[] = [
                'bank_name' => $employee->bank_name,
                'account_number' => $employee->bank_account_number,
                'iban' => $employee->bank_iban,
                'employee_name' => $employee->full_name,
                'employee_number' => $employee->employee_number,
                'amount' => $payslip->net_salary,
                'reference' => $payslip->payslip_number,
            ];
        }

        // Generate CSV
        $output = "Bank Name,Account Number,IBAN,Employee Name,Employee Number,Amount,Reference\n";
        foreach ($lines as $line) {
            $output .= implode(',', array_map(function ($v) {
                return '"'.str_replace('"', '""', $v).'"';
            }, $line))."\n";
        }

        return $output;
    }

    /**
     * Get employee payslip history.
     */
    public function getEmployeePayslipHistory(Employee $employee, int $limit = 12): Collection
    {
        return Payslip::where('employee_id', $employee->id)
            ->whereIn('status', [Payslip::STATUS_APPROVED, Payslip::STATUS_PAID])
            ->with('payrollPeriod')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Recalculate single payslip.
     */
    public function recalculatePayslip(Payslip $payslip): void
    {
        $payslip->recalculateTotals();
        $payslip->payrollPeriod->recalculateTotals();
    }
}
