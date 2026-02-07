<?php

namespace Gopos\Filament\Clusters\HumanResources\Resources\LeaveRequests\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;
use Gopos\Filament\Clusters\HumanResources\Resources\LeaveRequests\LeaveRequestResource;

class ViewLeaveRequest extends ViewRecord
{
    protected static string $resource = LeaveRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label(__('Approve'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status === 'pending')
                ->action(fn () => $this->record->approve(auth()->id())),
            Action::make('reject')
                ->label(__('Reject'))
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->form([
                    Textarea::make('rejection_reason')
                        ->label(__('Rejection Reason'))
                        ->required(),
                ])
                ->visible(fn () => $this->record->status === 'pending')
                ->action(fn (array $data) => $this->record->reject(auth()->id(), $data['rejection_reason'])),
        ];
    }
}
