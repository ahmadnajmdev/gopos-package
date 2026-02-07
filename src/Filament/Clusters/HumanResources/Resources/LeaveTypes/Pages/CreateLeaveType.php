<?php

namespace Gopos\Filament\Clusters\HumanResources\Resources\LeaveTypes\Pages;

use Filament\Resources\Pages\CreateRecord;
use Gopos\Filament\Clusters\HumanResources\Resources\LeaveTypes\LeaveTypeResource;

class CreateLeaveType extends CreateRecord
{
    protected static string $resource = LeaveTypeResource::class;
}
