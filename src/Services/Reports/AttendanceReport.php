<?php

namespace Gopos\Services\Reports;

use Gopos\Models\Attendance;
use Illuminate\Support\Collection;

class AttendanceReport extends BaseReport
{
    protected string $title = 'Attendance Report';

    protected bool $showTotals = true;

    protected array $columns = [
        'employee_name' => ['label' => 'Employee Name', 'type' => 'text'],
        'department' => ['label' => 'Department', 'type' => 'text'],
        'working_days' => ['label' => 'Working Days', 'type' => 'number'],
        'present_days' => ['label' => 'Present Days', 'type' => 'number'],
        'absent_days' => ['label' => 'Absent Days', 'type' => 'number'],
        'late_days' => ['label' => 'Late Days', 'type' => 'number'],
        'leave_days' => ['label' => 'Leave Days', 'type' => 'number'],
        'overtime_hours' => ['label' => 'Overtime Hours', 'type' => 'number'],
        'worked_hours' => ['label' => 'Worked Hours', 'type' => 'number'],
    ];

    protected array $totalColumns = ['working_days', 'present_days', 'absent_days', 'late_days', 'leave_days', 'overtime_hours', 'worked_hours'];

    public function getData(string $startDate, string $endDate): Collection
    {
        return Attendance::query()
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['employee.department'])
            ->get()
            ->groupBy('employee_id')
            ->map(function (Collection $attendances) {
                $employee = $attendances->first()->employee;

                if (! $employee) {
                    return null;
                }

                return [
                    'employee_name' => $employee->full_name,
                    'department' => $employee->department?->localized_name ?? __('N/A'),
                    'working_days' => $attendances->count(),
                    'present_days' => $attendances->whereIn('status', [Attendance::STATUS_PRESENT, Attendance::STATUS_LATE])->count(),
                    'absent_days' => $attendances->where('status', Attendance::STATUS_ABSENT)->count(),
                    'late_days' => $attendances->where('status', Attendance::STATUS_LATE)->count(),
                    'leave_days' => $attendances->where('status', Attendance::STATUS_LEAVE)->count(),
                    'overtime_hours' => round($attendances->sum('overtime_hours'), 2),
                    'worked_hours' => round($attendances->sum('worked_hours'), 2),
                ];
            })
            ->filter()
            ->sortBy('employee_name')
            ->values();
    }
}
