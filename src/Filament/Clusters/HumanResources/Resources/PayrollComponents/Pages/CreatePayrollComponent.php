<?php

namespace Gopos\Filament\Clusters\HumanResources\Resources\PayrollComponents\Pages;

use Filament\Resources\Pages\CreateRecord;
use Gopos\Filament\Clusters\HumanResources\Resources\PayrollComponents\PayrollComponentResource;

class CreatePayrollComponent extends CreateRecord
{
    protected static string $resource = PayrollComponentResource::class;
}
