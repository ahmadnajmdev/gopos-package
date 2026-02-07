<?php

namespace Gopos\Filament\Clusters\HumanResources\Resources\PayrollComponents\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Gopos\Filament\Clusters\HumanResources\Resources\PayrollComponents\PayrollComponentResource;

class ListPayrollComponents extends ListRecords
{
    protected static string $resource = PayrollComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
