<?php

namespace Gopos\Listeners;

use Gopos\Events\LeaveApproved;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class UpdateLeaveBalance implements ShouldQueue
{
    public function handle(LeaveApproved $event): void
    {
        $request = $event->leaveRequest;
        $employee = $request->employee;

        // Get or create leave balance for the year
        $balance = $employee->leaveBalances()
            ->where('leave_type_id', $request->leave_type_id)
            ->where('year', $request->start_date->year)
            ->first();

        if ($balance) {
            // Deduct from available days
            $balance->decrement('available_days', $request->days);
            $balance->increment('used_days', $request->days);

            Log::info('Leave balance updated', [
                'employee_id' => $employee->id,
                'leave_type_id' => $request->leave_type_id,
                'days_used' => $request->days,
                'remaining_balance' => $balance->available_days,
            ]);
        } else {
            Log::warning('Leave balance not found for employee', [
                'employee_id' => $employee->id,
                'leave_type_id' => $request->leave_type_id,
                'year' => $request->start_date->year,
            ]);
        }
    }
}
