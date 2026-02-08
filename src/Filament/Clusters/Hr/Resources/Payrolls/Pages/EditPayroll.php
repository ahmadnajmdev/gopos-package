<?php

namespace Gopos\Filament\Clusters\Hr\Resources\Payrolls\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Gopos\Filament\Clusters\Hr\Resources\Payrolls\PayrollResource;

class EditPayroll extends EditRecord
{
    protected static string $resource = PayrollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
