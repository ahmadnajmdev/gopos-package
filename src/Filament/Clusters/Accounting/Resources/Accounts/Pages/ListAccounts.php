<?php

namespace Gopos\Filament\Clusters\Accounting\Resources\Accounts\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Gopos\Filament\Clusters\Accounting\Resources\Accounts\AccountResource;
use Gopos\Services\ChartOfAccountsService;

class ListAccounts extends ListRecords
{
    protected static string $resource = AccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('initialize')
                ->label(__('Initialize Default Accounts'))
                ->icon('heroicon-o-sparkles')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading(__('Initialize Chart of Accounts'))
                ->modalDescription(__('This will create the default chart of accounts for your business. Existing accounts will not be affected.'))
                ->action(function () {
                    $service = app(ChartOfAccountsService::class);
                    $service->createDefaultAccounts();

                    Notification::make()
                        ->title(__('Default accounts created'))
                        ->success()
                        ->send();
                })
                ->visible(fn () => \Gopos\Models\Account::count() === 0),
            CreateAction::make(),
        ];
    }
}
