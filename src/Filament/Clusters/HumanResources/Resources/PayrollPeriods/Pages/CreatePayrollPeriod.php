<?php

namespace Gopos\Filament\Clusters\HumanResources\Resources\PayrollPeriods\Pages;

use Filament\Resources\Pages\CreateRecord;
use Gopos\Filament\Clusters\HumanResources\Resources\PayrollPeriods\PayrollPeriodResource;

class CreatePayrollPeriod extends CreateRecord
{
    protected static string $resource = PayrollPeriodResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = 'draft';

        return $data;
    }
}
