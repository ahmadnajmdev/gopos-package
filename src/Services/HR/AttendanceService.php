<?php

namespace Gopos\Services\HR;

use Carbon\Carbon;
use Gopos\Models\Attendance;
use Gopos\Models\Employee;
use Gopos\Models\Holiday;
use Gopos\Models\WorkSchedule;
use Illuminate\Support\Collection;

class AttendanceService
{
    /**
     * Clock in employee.
     */
    public function clockIn(Employee $employee, ?string $location = null, ?string $ip = null): Attendance
    {
        $today = now()->toDateString();

        // Get or create attendance record for today
        $attendance = Attendance::firstOrCreate(
            [
                'employee_id' => $employee->id,
                'date' => $today,
            ],
            [
                'status' => Attendance::STATUS_PRESENT,
            ]
        );

        // Perform clock in
        $attendance->clockIn($location, $ip);

        return $attendance;
    }

    /**
     * Clock out employee.
     */
    public function clockOut(Employee $employee, ?string $location = null, ?string $ip = null): ?Attendance
    {
        $today = now()->toDateString();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->whereNotNull('clock_in')
            ->whereNull('clock_out')
            ->first();

        if (! $attendance) {
            return null;
        }

        $attendance->clockOut($location, $ip);

        return $attendance;
    }

    /**
     * Get today's attendance status.
     */
    public function getTodayStatus(Employee $employee): ?Attendance
    {
        return Attendance::where('employee_id', $employee->id)
            ->where('date', now()->toDateString())
            ->first();
    }

    /**
     * Generate attendance records for month.
     */
    public function generateMonthlyRecords(int $year, int $month, ?Collection $employees = null): void
    {
        $employees = $employees ?? Employee::active()->get();
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        foreach ($employees as $employee) {
            $this->generateRecordsForEmployee($employee, $startDate, $endDate);
        }
    }

    /**
     * Generate attendance records for employee in date range.
     */
    public function generateRecordsForEmployee(Employee $employee, Carbon $startDate, Carbon $endDate): void
    {
        $schedule = $employee->workSchedule ?? WorkSchedule::getDefault();
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            // Skip if record already exists
            $exists = Attendance::where('employee_id', $employee->id)
                ->where('date', $currentDate->toDateString())
                ->exists();

            if (! $exists) {
                $status = $this->determineStatus($currentDate, $schedule);

                Attendance::create([
                    'employee_id' => $employee->id,
                    'date' => $currentDate->toDateString(),
                    'status' => $status,
                ]);
            }

            $currentDate->addDay();
        }
    }

    /**
     * Determine attendance status for a date.
     */
    protected function determineStatus(Carbon $date, ?WorkSchedule $schedule): string
    {
        // Check if holiday
        if (Holiday::isHoliday($date)) {
            return Attendance::STATUS_HOLIDAY;
        }

        // Check if weekend (based on schedule)
        if ($schedule && ! $schedule->isWorkingDay($date->dayOfWeek)) {
            return Attendance::STATUS_WEEKEND;
        }

        // Default to absent (will be updated when clocking in)
        return Attendance::STATUS_ABSENT;
    }

    /**
     * Get attendance summary for employee.
     */
    public function getEmployeeSummary(Employee $employee, int $year, int $month): array
    {
        $attendances = Attendance::where('employee_id', $employee->id)
            ->forMonth($year, $month)
            ->get();

        return [
            'total_days' => $attendances->count(),
            'present_days' => $attendances->whereIn('status', [Attendance::STATUS_PRESENT, Attendance::STATUS_LATE])->count(),
            'absent_days' => $attendances->where('status', Attendance::STATUS_ABSENT)->count(),
            'late_days' => $attendances->where('status', Attendance::STATUS_LATE)->count(),
            'half_days' => $attendances->where('status', Attendance::STATUS_HALF_DAY)->count(),
            'leave_days' => $attendances->where('status', Attendance::STATUS_LEAVE)->count(),
            'holiday_days' => $attendances->where('status', Attendance::STATUS_HOLIDAY)->count(),
            'weekend_days' => $attendances->where('status', Attendance::STATUS_WEEKEND)->count(),
            'total_worked_hours' => $attendances->sum('worked_hours'),
            'total_overtime_hours' => $attendances->sum('overtime_hours'),
            'total_late_minutes' => $attendances->sum('late_minutes'),
            'total_early_leave_minutes' => $attendances->sum('early_leave_minutes'),
        ];
    }

    /**
     * Get department attendance summary.
     */
    public function getDepartmentSummary(int $departmentId, string $date): array
    {
        $employees = Employee::active()->byDepartment($departmentId)->pluck('id');

        $attendances = Attendance::whereIn('employee_id', $employees)
            ->where('date', $date)
            ->get();

        return [
            'total_employees' => $employees->count(),
            'present' => $attendances->whereIn('status', [Attendance::STATUS_PRESENT, Attendance::STATUS_LATE])->count(),
            'absent' => $attendances->where('status', Attendance::STATUS_ABSENT)->count(),
            'late' => $attendances->where('status', Attendance::STATUS_LATE)->count(),
            'on_leave' => $attendances->where('status', Attendance::STATUS_LEAVE)->count(),
            'clocked_in' => $attendances->whereNotNull('clock_in')->whereNull('clock_out')->count(),
        ];
    }

    /**
     * Mark employee as on leave.
     */
    public function markAsLeave(Employee $employee, string $date): Attendance
    {
        return Attendance::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'date' => $date,
            ],
            [
                'status' => Attendance::STATUS_LEAVE,
            ]
        );
    }

    /**
     * Get late arrivals for date.
     */
    public function getLateArrivals(string $date): Collection
    {
        return Attendance::where('date', $date)
            ->where('is_late', true)
            ->with('employee')
            ->orderByDesc('late_minutes')
            ->get();
    }

    /**
     * Get early leaves for date.
     */
    public function getEarlyLeaves(string $date): Collection
    {
        return Attendance::where('date', $date)
            ->where('early_leave', true)
            ->with('employee')
            ->orderByDesc('early_leave_minutes')
            ->get();
    }

    /**
     * Get currently clocked in employees.
     */
    public function getCurrentlyClockedIn(): Collection
    {
        return Attendance::where('date', now()->toDateString())
            ->whereNotNull('clock_in')
            ->whereNull('clock_out')
            ->with('employee')
            ->get();
    }

    /**
     * Bulk update attendance status.
     */
    public function bulkUpdateStatus(array $attendanceIds, string $status, ?int $approverId = null): int
    {
        return Attendance::whereIn('id', $attendanceIds)
            ->update([
                'status' => $status,
                'approved_by' => $approverId,
            ]);
    }

    /**
     * Calculate working days in period.
     */
    public function calculateWorkingDays(Carbon $startDate, Carbon $endDate, ?WorkSchedule $schedule = null): int
    {
        $schedule = $schedule ?? WorkSchedule::getDefault();
        $workingDays = 0;
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            if (! Holiday::isHoliday($currentDate)) {
                if (! $schedule || $schedule->isWorkingDay($currentDate->dayOfWeek)) {
                    $workingDays++;
                }
            }
            $currentDate->addDay();
        }

        return $workingDays;
    }
}
