<?php

namespace Gopos\Services\Reports;

use Carbon\Carbon;
use Gopos\Models\LeaveBalance;
use Illuminate\Support\Collection;

class LeaveReport extends BaseReport
{
    protected string $title = 'Leave Report';

    protected bool $showTotals = true;

    protected array $columns = [
        'employee_name' => ['label' => 'Employee Name', 'type' => 'text'],
        'department' => ['label' => 'Department', 'type' => 'text'],
        'leave_type' => ['label' => 'Leave Type', 'type' => 'text'],
        'entitled_days' => ['label' => 'Entitled Days', 'type' => 'number'],
        'used_days' => ['label' => 'Used Days', 'type' => 'number'],
        'pending_days' => ['label' => 'Pending Days', 'type' => 'number'],
        'carried_forward' => ['label' => 'Carried Forward', 'type' => 'number'],
        'available_balance' => ['label' => 'Available Balance', 'type' => 'number'],
    ];

    protected array $totalColumns = ['entitled_days', 'used_days', 'pending_days', 'carried_forward', 'available_balance'];

    public function getData(string $startDate, string $endDate): Collection
    {
        $year = Carbon::parse($startDate)->year;

        return LeaveBalance::query()
            ->where('year', $year)
            ->with(['employee.department', 'leaveType'])
            ->whereHas('employee')
            ->get()
            ->map(function (LeaveBalance $balance) {
                return [
                    'employee_name' => $balance->employee->full_name,
                    'department' => $balance->employee->department?->localized_name ?? __('N/A'),
                    'leave_type' => $balance->leaveType?->localized_name ?? __('N/A'),
                    'entitled_days' => round($balance->entitled_days, 2),
                    'used_days' => round($balance->used_days, 2),
                    'pending_days' => round($balance->pending_days, 2),
                    'carried_forward' => round($balance->carried_forward, 2),
                    'available_balance' => round($balance->available, 2),
                ];
            })
            ->sortBy('employee_name')
            ->values();
    }
}
