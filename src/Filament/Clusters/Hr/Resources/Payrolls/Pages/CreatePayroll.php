<?php

namespace Gopos\Filament\Clusters\Hr\Resources\Payrolls\Pages;

use Filament\Resources\Pages\CreateRecord;
use Gopos\Filament\Clusters\Hr\Resources\Payrolls\PayrollResource;

class CreatePayroll extends CreateRecord
{
    protected static string $resource = PayrollResource::class;
}
