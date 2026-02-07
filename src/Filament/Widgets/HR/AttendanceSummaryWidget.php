<?php

namespace Gopos\Filament\Widgets\HR;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Gopos\Models\Attendance;
use Gopos\Models\Employee;
use Gopos\Models\LeaveRequest;
use Illuminate\Support\HtmlString;

class AttendanceSummaryWidget extends BaseWidget
{
    protected static bool $isLazy = true;

    protected static ?int $sort = 16;

    protected function getStats(): array
    {
        // Today's attendance
        $presentToday = Attendance::whereDate('date', today())
            ->where('status', Attendance::STATUS_PRESENT)
            ->count();

        $absentToday = Attendance::whereDate('date', today())
            ->where('status', Attendance::STATUS_ABSENT)
            ->count();

        $lateToday = Attendance::whereDate('date', today())
            ->where('is_late', true)
            ->count();

        // On leave today
        $onLeaveToday = LeaveRequest::where('status', LeaveRequest::STATUS_APPROVED)
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->count();

        // Total active employees
        $totalEmployees = Employee::where('status', 'active')->count();

        return [
            Stat::make(__('Present Today'), number_format($presentToday))
                ->description(__('Checked in'))
                ->icon('heroicon-o-check-circle')
                ->value(new HtmlString('<span class="text-success-600">'.number_format($presentToday).'</span>'))
                ->color('success'),

            Stat::make(__('Absent Today'), number_format($absentToday))
                ->description(__('Not checked in'))
                ->icon('heroicon-o-x-circle')
                ->value(new HtmlString('<span class="text-danger-600">'.number_format($absentToday).'</span>'))
                ->color('danger'),

            Stat::make(__('Late Today'), number_format($lateToday))
                ->description(__('Arrived late'))
                ->icon('heroicon-o-clock')
                ->value(new HtmlString('<span class="text-warning-600">'.number_format($lateToday).'</span>'))
                ->color('warning'),

            Stat::make(__('On Leave'), number_format($onLeaveToday))
                ->description(__('Approved leave'))
                ->icon('heroicon-o-calendar')
                ->color('info'),

            Stat::make(__('Total Employees'), number_format($totalEmployees))
                ->description(__('Active staff'))
                ->icon('heroicon-o-users')
                ->color('primary'),
        ];
    }
}
