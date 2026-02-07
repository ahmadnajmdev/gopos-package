<?php

namespace Gopos\Services\HR;

use Carbon\Carbon;
use Gopos\Models\Employee;
use Gopos\Models\LeaveBalance;
use Gopos\Models\LeavePolicy;
use Gopos\Models\LeaveRequest;
use Gopos\Models\LeaveType;
use Illuminate\Support\Collection;

class LeaveService
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Submit leave request.
     */
    public function submitRequest(
        Employee $employee,
        int $leaveTypeId,
        Carbon $startDate,
        Carbon $endDate,
        bool $isHalfDay = false,
        ?string $halfDayType = null,
        ?string $reason = null,
        ?string $attachment = null
    ): LeaveRequest {
        $leaveType = LeaveType::findOrFail($leaveTypeId);
        $days = LeaveRequest::calculateDays($startDate, $endDate, $isHalfDay);

        // Validate request
        $this->validateRequest($employee, $leaveType, $startDate, $days);

        // Create request
        $request = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveTypeId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days' => $days,
            'is_half_day' => $isHalfDay,
            'half_day_type' => $halfDayType,
            'reason' => $reason,
            'attachment' => $attachment,
            'status' => LeaveRequest::STATUS_PENDING,
            'requested_at' => now(),
        ]);

        // Reserve days in balance
        $balance = $this->getOrCreateBalance($employee, $leaveTypeId);
        $balance->reserveDays($days);

        return $request;
    }

    /**
     * Validate leave request.
     */
    protected function validateRequest(Employee $employee, LeaveType $leaveType, Carbon $startDate, float $days): void
    {
        // Check notice period
        if (! $leaveType->isNoticePeriodSatisfied(now(), $startDate)) {
            throw new \Exception(__('Minimum notice period of :days days not met', [
                'days' => $leaveType->min_days_notice,
            ]));
        }

        // Check duration limits
        if (! $leaveType->isDurationWithinLimits($days)) {
            throw new \Exception(__('Leave duration exceeds maximum of :days consecutive days', [
                'days' => $leaveType->max_consecutive_days,
            ]));
        }

        // Check balance
        $balance = $this->getOrCreateBalance($employee, $leaveType->id);
        if (! $balance->hasSufficientBalance($days)) {
            throw new \Exception(__('Insufficient leave balance. Available: :available days', [
                'available' => $balance->available,
            ]));
        }

        // Check for overlapping requests
        $hasOverlap = LeaveRequest::where('employee_id', $employee->id)
            ->whereIn('status', [LeaveRequest::STATUS_PENDING, LeaveRequest::STATUS_APPROVED])
            ->where(function ($query) use ($startDate, $days) {
                $endDate = $startDate->copy()->addDays($days - 1);
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate]);
            })
            ->exists();

        if ($hasOverlap) {
            throw new \Exception(__('Leave request overlaps with an existing request'));
        }
    }

    /**
     * Approve leave request.
     */
    public function approveRequest(LeaveRequest $request, int $approverId): void
    {
        if (! $request->canBeApproved()) {
            throw new \Exception(__('This request cannot be approved'));
        }

        $request->approve($approverId);

        // Mark attendance as leave for the period
        $currentDate = $request->start_date->copy();
        while ($currentDate->lte($request->end_date)) {
            $this->attendanceService->markAsLeave($request->employee, $currentDate->toDateString());
            $currentDate->addDay();
        }
    }

    /**
     * Reject leave request.
     */
    public function rejectRequest(LeaveRequest $request, int $approverId, string $reason): void
    {
        if (! $request->canBeRejected()) {
            throw new \Exception(__('This request cannot be rejected'));
        }

        $request->reject($approverId, $reason);
    }

    /**
     * Cancel leave request.
     */
    public function cancelRequest(LeaveRequest $request, string $reason): void
    {
        if (! $request->canBeCancelled()) {
            throw new \Exception(__('This request cannot be cancelled'));
        }

        $request->cancel($reason);
    }

    /**
     * Get or create leave balance.
     */
    public function getOrCreateBalance(Employee $employee, int $leaveTypeId, ?int $year = null): LeaveBalance
    {
        $year = $year ?? now()->year;

        $balance = LeaveBalance::where('employee_id', $employee->id)
            ->where('leave_type_id', $leaveTypeId)
            ->where('year', $year)
            ->first();

        if (! $balance) {
            $balance = $this->createBalance($employee, $leaveTypeId, $year);
        }

        return $balance;
    }

    /**
     * Create leave balance.
     */
    protected function createBalance(Employee $employee, int $leaveTypeId, int $year): LeaveBalance
    {
        $leaveType = LeaveType::findOrFail($leaveTypeId);
        $policy = LeavePolicy::findForEmployee($employee, $leaveTypeId);

        // Calculate entitled days
        $entitledDays = $policy ? $policy->entitled_days : $leaveType->default_days;

        // Calculate carry forward from previous year
        $carriedForward = 0;
        if ($leaveType->is_carry_forward) {
            $previousBalance = LeaveBalance::where('employee_id', $employee->id)
                ->where('leave_type_id', $leaveTypeId)
                ->where('year', $year - 1)
                ->first();

            if ($previousBalance) {
                $remainingDays = $previousBalance->available;
                $maxCarryForward = $leaveType->max_carry_forward_days ?? $remainingDays;
                $carriedForward = min($remainingDays, $maxCarryForward);
            }
        }

        return LeaveBalance::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveTypeId,
            'year' => $year,
            'entitled_days' => $entitledDays,
            'carried_forward' => $carriedForward,
            'adjustment' => 0,
            'used_days' => 0,
            'pending_days' => 0,
        ]);
    }

    /**
     * Initialize balances for new year.
     */
    public function initializeYearBalances(int $year, ?Collection $employees = null): void
    {
        $employees = $employees ?? Employee::active()->get();
        $leaveTypes = LeaveType::active()->get();

        foreach ($employees as $employee) {
            foreach ($leaveTypes as $leaveType) {
                $this->getOrCreateBalance($employee, $leaveType->id, $year);
            }
        }
    }

    /**
     * Initialize balances for new employee.
     */
    public function initializeEmployeeBalances(Employee $employee): void
    {
        $year = now()->year;
        $leaveTypes = LeaveType::active()->get();

        foreach ($leaveTypes as $leaveType) {
            $this->getOrCreateBalance($employee, $leaveType->id, $year);
        }
    }

    /**
     * Adjust leave balance.
     */
    public function adjustBalance(LeaveBalance $balance, float $adjustment, string $reason): void
    {
        $balance->update([
            'adjustment' => $balance->adjustment + $adjustment,
        ]);

        // Log the adjustment
        \Log::info('Leave balance adjusted', [
            'balance_id' => $balance->id,
            'employee_id' => $balance->employee_id,
            'adjustment' => $adjustment,
            'reason' => $reason,
        ]);
    }

    /**
     * Get employee leave summary.
     */
    public function getEmployeeSummary(Employee $employee, ?int $year = null): array
    {
        $year = $year ?? now()->year;
        $balances = LeaveBalance::where('employee_id', $employee->id)
            ->where('year', $year)
            ->with('leaveType')
            ->get();

        $summary = [];
        foreach ($balances as $balance) {
            $summary[] = [
                'leave_type' => $balance->leaveType->localized_name,
                'entitled' => $balance->total_entitled,
                'used' => $balance->used_days,
                'pending' => $balance->pending_days,
                'available' => $balance->available,
                'utilized_percentage' => $balance->utilized_percentage,
            ];
        }

        return $summary;
    }

    /**
     * Get pending requests for approver.
     */
    public function getPendingRequestsForApprover(int $approverId): Collection
    {
        // Get employees managed by this user
        $managedEmployeeIds = Employee::where('manager_id', function ($query) use ($approverId) {
            $query->select('id')
                ->from('employees')
                ->where('user_id', $approverId);
        })->pluck('id');

        return LeaveRequest::whereIn('employee_id', $managedEmployeeIds)
            ->pending()
            ->with(['employee', 'leaveType'])
            ->orderBy('requested_at')
            ->get();
    }

    /**
     * Get leave calendar data.
     */
    public function getCalendarData(int $year, int $month, ?int $departmentId = null): array
    {
        $query = LeaveRequest::approved()
            ->whereYear('start_date', $year)
            ->whereMonth('start_date', $month)
            ->orWhere(function ($q) use ($year, $month) {
                $q->whereYear('end_date', $year)
                    ->whereMonth('end_date', $month);
            })
            ->with(['employee', 'leaveType']);

        if ($departmentId) {
            $query->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        $requests = $query->get();

        $calendar = [];
        foreach ($requests as $request) {
            $calendar[] = [
                'id' => $request->id,
                'employee' => $request->employee->localized_full_name,
                'leave_type' => $request->leaveType->localized_name,
                'start_date' => $request->start_date->format('Y-m-d'),
                'end_date' => $request->end_date->format('Y-m-d'),
                'days' => $request->days,
                'color' => $this->getLeaveTypeColor($request->leaveType),
            ];
        }

        return $calendar;
    }

    /**
     * Get color for leave type.
     */
    protected function getLeaveTypeColor(LeaveType $leaveType): string
    {
        $colors = [
            'annual' => '#4CAF50',
            'sick' => '#F44336',
            'maternity' => '#E91E63',
            'paternity' => '#9C27B0',
            'unpaid' => '#607D8B',
            'emergency' => '#FF5722',
            'bereavement' => '#795548',
        ];

        return $colors[$leaveType->code] ?? '#2196F3';
    }
}
