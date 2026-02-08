<?php

namespace Gopos\Services\Reports;

use Gopos\Models\Employee;
use Illuminate\Support\Collection;

class EmployeeHeadcountReport extends BaseReport
{
    protected string $title = 'Employee Headcount Report';

    protected bool $showTotals = false;

    protected array $columns = [
        'employee_number' => ['label' => 'Employee Number', 'type' => 'text'],
        'name' => ['label' => 'Name', 'type' => 'text'],
        'department' => ['label' => 'Department', 'type' => 'text'],
        'position' => ['label' => 'Position', 'type' => 'text'],
        'employment_type' => ['label' => 'Employment Type', 'type' => 'text'],
        'status' => ['label' => 'Status', 'type' => 'text'],
        'hire_date' => ['label' => 'Hire Date', 'type' => 'date'],
        'service_years' => ['label' => 'Service Years', 'type' => 'number'],
    ];

    public function getData(string $startDate, string $endDate): Collection
    {
        return Employee::query()
            ->with(['department', 'position'])
            ->whereDate('hire_date', '<=', $endDate)
            ->where(function ($query) use ($endDate) {
                $query->whereNull('termination_date')
                    ->orWhereDate('termination_date', '>=', $endDate);
            })
            ->orderBy('employee_number')
            ->get()
            ->map(function (Employee $employee) {
                return [
                    'employee_number' => $employee->employee_number,
                    'name' => $employee->full_name,
                    'department' => $employee->department?->localized_name ?? __('N/A'),
                    'position' => $employee->position?->localized_title ?? __('N/A'),
                    'employment_type' => $this->formatEmploymentType($employee->employment_type),
                    'status' => $this->formatStatus($employee->status),
                    'hire_date' => $employee->hire_date?->format('Y-m-d'),
                    'service_years' => $employee->service_years,
                ];
            });
    }

    private function formatEmploymentType(?string $type): string
    {
        return match ($type) {
            Employee::TYPE_FULL_TIME => __('Full Time'),
            Employee::TYPE_PART_TIME => __('Part Time'),
            Employee::TYPE_CONTRACT => __('Contract'),
            Employee::TYPE_TEMPORARY => __('Temporary'),
            Employee::TYPE_INTERN => __('Intern'),
            default => $type ?? __('N/A'),
        };
    }

    private function formatStatus(?string $status): string
    {
        return match ($status) {
            Employee::STATUS_ACTIVE => __('Active'),
            Employee::STATUS_ON_LEAVE => __('On Leave'),
            Employee::STATUS_SUSPENDED => __('Suspended'),
            Employee::STATUS_TERMINATED => __('Terminated'),
            Employee::STATUS_RESIGNED => __('Resigned'),
            default => $status ?? __('N/A'),
        };
    }
}
