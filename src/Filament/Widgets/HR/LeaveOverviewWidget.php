<?php

namespace Gopos\Filament\Widgets\HR;

use Filament\Widgets\ChartWidget;
use Gopos\Models\LeaveRequest;
use Illuminate\Contracts\Support\Htmlable;

class LeaveOverviewWidget extends ChartWidget
{
    protected static bool $isLazy = true;

    protected static ?int $sort = 17;

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '300px';

    public function getHeading(): string|Htmlable|null
    {
        return __('Leave Requests (This Month)');
    }

    protected function getData(): array
    {
        $requests = LeaveRequest::query()
            ->selectRaw('status, COUNT(*) as count')
            ->whereMonth('start_date', now()->month)
            ->whereYear('start_date', now()->year)
            ->groupBy('status')
            ->get();

        $statusLabels = [
            LeaveRequest::STATUS_PENDING => __('Pending'),
            LeaveRequest::STATUS_APPROVED => __('Approved'),
            LeaveRequest::STATUS_REJECTED => __('Rejected'),
            LeaveRequest::STATUS_CANCELLED => __('Cancelled'),
        ];

        $statusColors = [
            LeaveRequest::STATUS_PENDING => '#F59E0B',    // Amber
            LeaveRequest::STATUS_APPROVED => '#10B981',   // Green
            LeaveRequest::STATUS_REJECTED => '#EF4444',   // Red
            LeaveRequest::STATUS_CANCELLED => '#6B7280', // Gray
        ];

        $labels = [];
        $values = [];
        $colors = [];

        foreach ($requests as $request) {
            $status = $request->status;
            $labels[] = $statusLabels[$status] ?? ucfirst($status);
            $values[] = $request->count;
            $colors[] = $statusColors[$status] ?? '#6B7280';
        }

        return [
            'datasets' => [
                [
                    'data' => $values,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
