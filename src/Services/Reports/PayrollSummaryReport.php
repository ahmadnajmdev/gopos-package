<?php

namespace Gopos\Services\Reports;

use Gopos\Models\Payroll;
use Illuminate\Support\Collection;

class PayrollSummaryReport extends BaseReport
{
    protected string $title = 'Payroll Summary Report';

    protected bool $showTotals = true;

    protected array $columns = [
        'department_name' => ['label' => 'Department', 'type' => 'text'],
        'employee_count' => ['label' => 'Employees', 'type' => 'number'],
        'total_basic' => ['label' => 'Basic Salary', 'type' => 'currency'],
        'total_bonuses' => ['label' => 'Bonuses', 'type' => 'currency'],
        'total_overtime' => ['label' => 'Overtime', 'type' => 'currency'],
        'total_deductions' => ['label' => 'Deductions', 'type' => 'currency'],
        'total_net_pay' => ['label' => 'Net Pay', 'type' => 'currency'],
    ];

    protected array $totalColumns = ['employee_count', 'total_basic', 'total_bonuses', 'total_overtime', 'total_deductions', 'total_net_pay'];

    public function getData(string $startDate, string $endDate, ?int $branchId = null, bool $allBranches = false): Collection
    {
        $query = Payroll::query();

        if ($allBranches) {
            $query->withoutGlobalScope(filament()->getTenancyScopeName());
        } elseif ($branchId) {
            $query->withoutGlobalScope(filament()->getTenancyScopeName())
                ->where('branch_id', $branchId);
        }

        $payrolls = $query
            ->with('employee.department')
            ->whereBetween('pay_period_start', [$startDate, $endDate])
            ->get();

        return $payrolls->groupBy(fn ($payroll) => $payroll->employee?->department?->name ?? __('Unassigned'))
            ->map(function ($departmentPayrolls, $departmentName) {
                return [
                    'department_name' => $departmentName,
                    'employee_count' => $departmentPayrolls->unique('employee_id')->count(),
                    'total_basic' => $departmentPayrolls->sum('basic_salary'),
                    'total_bonuses' => $departmentPayrolls->sum('bonuses'),
                    'total_overtime' => $departmentPayrolls->sum('overtime_pay'),
                    'total_deductions' => $departmentPayrolls->sum('deductions'),
                    'total_net_pay' => $departmentPayrolls->sum('net_pay'),
                ];
            })
            ->values();
    }
}
