<?php

namespace Gopos\Filament\Clusters\Hr\Resources\Payrolls\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Gopos\Filament\Clusters\Hr\Resources\Payrolls\PayrollResource;

class ListPayrolls extends ListRecords
{
    protected static string $resource = PayrollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
