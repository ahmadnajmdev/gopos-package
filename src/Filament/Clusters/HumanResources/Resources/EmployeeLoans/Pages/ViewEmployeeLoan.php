<?php

namespace Gopos\Filament\Clusters\HumanResources\Resources\EmployeeLoans\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Gopos\Filament\Clusters\HumanResources\Resources\EmployeeLoans\EmployeeLoanResource;

class ViewEmployeeLoan extends ViewRecord
{
    protected static string $resource = EmployeeLoanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label(__('Approve'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status === 'pending')
                ->action(function () {
                    $this->record->approve(auth()->id());
                    $this->record->calculateLoan();
                    $this->record->activate();
                }),
            Action::make('reject')
                ->label(__('Reject'))
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status === 'pending')
                ->action(fn () => $this->record->update(['status' => 'rejected'])),
        ];
    }
}
