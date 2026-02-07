<?php

namespace Gopos\Filament\Clusters\Accounting\Resources\Expenses\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Gopos\Filament\Clusters\Accounting\Resources\Expenses\ExpenseResource;

class ManageExpenses extends ManageRecords
{
    protected static string $resource = ExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
