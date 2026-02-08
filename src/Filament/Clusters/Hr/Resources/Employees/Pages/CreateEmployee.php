<?php

namespace Gopos\Filament\Clusters\Hr\Resources\Employees\Pages;

use Filament\Resources\Pages\CreateRecord;
use Gopos\Filament\Clusters\Hr\Resources\Employees\EmployeeResource;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;
}
