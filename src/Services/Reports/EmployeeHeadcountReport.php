<?php

namespace Gopos\Services\Reports;

use Gopos\Enums\EmployeeStatus;
use Gopos\Models\Employee;
use Illuminate\Support\Collection;

class EmployeeHeadcountReport extends BaseReport
{
    protected string $title = 'Employee Headcount Report';

    protected bool $showTotals = true;

    protected array $columns = [
        'department_name' => ['label' => 'Department', 'type' => 'text'],
        'active_count' => ['label' => 'Active', 'type' => 'number'],
        'on_leave_count' => ['label' => 'On Leave', 'type' => 'number'],
        'suspended_count' => ['label' => 'Suspended', 'type' => 'number'],
        'terminated_count' => ['label' => 'Terminated', 'type' => 'number'],
        'resigned_count' => ['label' => 'Resigned', 'type' => 'number'],
        'total' => ['label' => 'Total', 'type' => 'number'],
    ];

    protected array $totalColumns = ['active_count', 'on_leave_count', 'suspended_count', 'terminated_count', 'resigned_count', 'total'];

    public function getData(string $startDate, string $endDate): Collection
    {
        $employees = Employee::query()
            ->with('department')
            ->whereBetween('hire_date', [$startDate, $endDate])
            ->orWhere('hire_date', '<=', $endDate)
            ->get();

        return $employees->groupBy(fn ($employee) => $employee->department?->name ?? __('Unassigned'))
            ->map(function ($departmentEmployees, $departmentName) {
                $active = $departmentEmployees->where('status', EmployeeStatus::Active)->count();
                $onLeave = $departmentEmployees->where('status', EmployeeStatus::OnLeave)->count();
                $suspended = $departmentEmployees->where('status', EmployeeStatus::Suspended)->count();
                $terminated = $departmentEmployees->where('status', EmployeeStatus::Terminated)->count();
                $resigned = $departmentEmployees->where('status', EmployeeStatus::Resigned)->count();

                return [
                    'department_name' => $departmentName,
                    'active_count' => $active,
                    'on_leave_count' => $onLeave,
                    'suspended_count' => $suspended,
                    'terminated_count' => $terminated,
                    'resigned_count' => $resigned,
                    'total' => $active + $onLeave + $suspended + $terminated + $resigned,
                ];
            })
            ->values();
    }
}
