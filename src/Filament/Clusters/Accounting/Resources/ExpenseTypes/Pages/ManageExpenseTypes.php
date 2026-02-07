<?php

namespace Gopos\Filament\Clusters\Accounting\Resources\ExpenseTypes\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Gopos\Filament\Clusters\Accounting\Resources\ExpenseTypes\ExpenseTypeResource;

class ManageExpenseTypes extends ManageRecords
{
    protected static string $resource = ExpenseTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
