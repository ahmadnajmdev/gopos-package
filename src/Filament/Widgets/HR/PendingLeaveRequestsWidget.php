<?php

namespace Gopos\Filament\Widgets\HR;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Gopos\Models\LeaveRequest;
use Illuminate\Database\Eloquent\Builder;

class PendingLeaveRequestsWidget extends TableWidget
{
    protected static bool $isLazy = true;

    protected static ?int $sort = 18;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => LeaveRequest::query()
                ->with(['employee', 'leaveType'])
                ->where('status', LeaveRequest::STATUS_PENDING)
                ->orderBy('start_date')
                ->limit(5)
            )
            ->heading(__('Pending Leave Requests'))
            ->columns([
                TextColumn::make('employee.name')
                    ->label(__('Employee'))
                    ->searchable(),

                TextColumn::make('leaveType.name')
                    ->label(__('Leave Type')),

                TextColumn::make('start_date')
                    ->label(__('From'))
                    ->date()
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label(__('To'))
                    ->date(),

                TextColumn::make('days')
                    ->label(__('Days'))
                    ->numeric(),

                TextColumn::make('requested_at')
                    ->label(__('Requested'))
                    ->since()
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color('warning'),
            ])
            ->paginated(false)
            ->emptyStateHeading(__('No pending requests'))
            ->emptyStateDescription(__('All leave requests have been processed'));
    }
}
