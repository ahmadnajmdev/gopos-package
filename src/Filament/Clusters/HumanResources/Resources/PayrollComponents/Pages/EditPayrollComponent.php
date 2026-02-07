<?php

namespace Gopos\Filament\Clusters\HumanResources\Resources\PayrollComponents\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Gopos\Filament\Clusters\HumanResources\Resources\PayrollComponents\PayrollComponentResource;

class EditPayrollComponent extends EditRecord
{
    protected static string $resource = PayrollComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
