<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'clock_in',
        'clock_out',
        'break_start',
        'break_end',
        'worked_hours',
        'overtime_hours',
        'status',
        'is_late',
        'late_minutes',
        'early_leave',
        'early_leave_minutes',
        'clock_in_location',
        'clock_out_location',
        'clock_in_ip',
        'clock_out_ip',
        'notes',
        'approved_by',
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime:H:i',
        'clock_out' => 'datetime:H:i',
        'break_start' => 'datetime:H:i',
        'break_end' => 'datetime:H:i',
        'worked_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'is_late' => 'boolean',
        'late_minutes' => 'integer',
        'early_leave' => 'boolean',
        'early_leave_minutes' => 'integer',
    ];

    public const STATUS_PRESENT = 'present';

    public const STATUS_ABSENT = 'absent';

    public const STATUS_LATE = 'late';

    public const STATUS_HALF_DAY = 'half_day';

    public const STATUS_HOLIDAY = 'holiday';

    public const STATUS_WEEKEND = 'weekend';

    public const STATUS_LEAVE = 'leave';

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Clock in.
     */
    public function clockIn(?string $location = null, ?string $ip = null): void
    {
        $now = now();
        $schedule = $this->employee->workSchedule;

        $isLate = false;
        $lateMinutes = 0;

        if ($schedule) {
            $isLate = $schedule->isLate($now->format('H:i'));
            $lateMinutes = $schedule->getLateMinutes($now->format('H:i'));
        }

        $this->update([
            'clock_in' => $now->format('H:i:s'),
            'clock_in_location' => $location,
            'clock_in_ip' => $ip,
            'is_late' => $isLate,
            'late_minutes' => $lateMinutes,
            'status' => $isLate ? self::STATUS_LATE : self::STATUS_PRESENT,
        ]);
    }

    /**
     * Clock out.
     */
    public function clockOut(?string $location = null, ?string $ip = null): void
    {
        $now = now();
        $schedule = $this->employee->workSchedule;

        $earlyLeave = false;
        $earlyLeaveMinutes = 0;

        if ($schedule) {
            $earlyLeave = $schedule->isEarlyLeave($now->format('H:i'));
            $earlyLeaveMinutes = $schedule->getEarlyLeaveMinutes($now->format('H:i'));
        }

        // Calculate worked hours
        $workedHours = $this->calculateWorkedHours($now);
        $overtimeHours = $this->calculateOvertimeHours($workedHours, $schedule);

        $this->update([
            'clock_out' => $now->format('H:i:s'),
            'clock_out_location' => $location,
            'clock_out_ip' => $ip,
            'early_leave' => $earlyLeave,
            'early_leave_minutes' => $earlyLeaveMinutes,
            'worked_hours' => $workedHours,
            'overtime_hours' => $overtimeHours,
        ]);
    }

    /**
     * Calculate worked hours.
     */
    protected function calculateWorkedHours($clockOutTime): float
    {
        if (! $this->clock_in) {
            return 0;
        }

        $clockIn = \Carbon\Carbon::parse($this->date->format('Y-m-d').' '.$this->clock_in);
        $clockOut = \Carbon\Carbon::parse($this->date->format('Y-m-d').' '.$clockOutTime->format('H:i:s'));

        $totalMinutes = $clockOut->diffInMinutes($clockIn);

        // Subtract break time if applicable
        if ($this->break_start && $this->break_end) {
            $breakStart = \Carbon\Carbon::parse($this->date->format('Y-m-d').' '.$this->break_start);
            $breakEnd = \Carbon\Carbon::parse($this->date->format('Y-m-d').' '.$this->break_end);
            $breakMinutes = $breakEnd->diffInMinutes($breakStart);
            $totalMinutes -= $breakMinutes;
        }

        return round($totalMinutes / 60, 2);
    }

    /**
     * Calculate overtime hours.
     */
    protected function calculateOvertimeHours(float $workedHours, ?WorkSchedule $schedule): float
    {
        if (! $schedule) {
            return 0;
        }

        $standardHours = $schedule->working_hours;

        if ($workedHours > $standardHours) {
            return round($workedHours - $standardHours, 2);
        }

        return 0;
    }

    /**
     * Start break.
     */
    public function startBreak(): void
    {
        $this->update(['break_start' => now()->format('H:i:s')]);
    }

    /**
     * End break.
     */
    public function endBreak(): void
    {
        $this->update(['break_end' => now()->format('H:i:s')]);
    }

    /**
     * Get break duration in minutes.
     */
    public function getBreakDurationAttribute(): int
    {
        if (! $this->break_start || ! $this->break_end) {
            return 0;
        }

        $start = \Carbon\Carbon::parse($this->break_start);
        $end = \Carbon\Carbon::parse($this->break_end);

        return $end->diffInMinutes($start);
    }

    /**
     * Check if clocked in.
     */
    public function isClockedIn(): bool
    {
        return ! is_null($this->clock_in) && is_null($this->clock_out);
    }

    /**
     * Check if completed.
     */
    public function isCompleted(): bool
    {
        return ! is_null($this->clock_in) && ! is_null($this->clock_out);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->whereYear('date', $year)->whereMonth('date', $month);
    }

    public function scopePresent($query)
    {
        return $query->whereIn('status', [self::STATUS_PRESENT, self::STATUS_LATE]);
    }

    public function scopeAbsent($query)
    {
        return $query->where('status', self::STATUS_ABSENT);
    }
}
