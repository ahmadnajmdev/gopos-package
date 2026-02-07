<?php

namespace Gopos\Filament\Clusters\Accounting\Resources\IncomeTypes\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Gopos\Filament\Clusters\Accounting\Resources\IncomeTypes\IncomeTypeResource;

class ManageIncomeTypes extends ManageRecords
{
    protected static string $resource = IncomeTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
