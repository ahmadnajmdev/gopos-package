<?php

namespace Gopos\Filament\Clusters\HumanResources\Resources\PayrollPeriods\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Gopos\Events\PayrollApproved;
use Gopos\Filament\Clusters\HumanResources\Resources\PayrollPeriods\PayrollPeriodResource;
use Gopos\Jobs\ProcessPayrollJob;

class ViewPayrollPeriod extends ViewRecord
{
    protected static string $resource = PayrollPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('process')
                ->label(__('Process Payroll'))
                ->icon('heroicon-o-play')
                ->color('info')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->canBeProcessed())
                ->action(function () {
                    $this->record->startProcessing();
                    ProcessPayrollJob::dispatch($this->record, auth()->id());
                    Notification::make()
                        ->title(__('Payroll processing started'))
                        ->success()
                        ->send();
                }),
            Action::make('approve')
                ->label(__('Approve'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->canBeApproved())
                ->action(function () {
                    $this->record->approve(auth()->id());
                    event(new PayrollApproved($this->record));
                    Notification::make()
                        ->title(__('Payroll approved'))
                        ->success()
                        ->send();
                }),
            Action::make('pay')
                ->label(__('Mark as Paid'))
                ->icon('heroicon-o-banknotes')
                ->color('primary')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->canBePaid())
                ->action(function () {
                    $this->record->markPaid(auth()->id());
                    Notification::make()
                        ->title(__('Payroll marked as paid'))
                        ->success()
                        ->send();
                }),
        ];
    }
}
