<?php

namespace Gopos\Filament\Clusters\Accounting\Resources\Incomes\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Gopos\Filament\Clusters\Accounting\Resources\Incomes\IncomeResource;

class ManageIncomes extends ManageRecords
{
    protected static string $resource = IncomeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
