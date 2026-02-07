<?php

namespace Gopos\Filament\Clusters\HumanResources\Resources\WorkSchedules\Pages;

use Filament\Resources\Pages\CreateRecord;
use Gopos\Filament\Clusters\HumanResources\Resources\WorkSchedules\WorkScheduleResource;

class CreateWorkSchedule extends CreateRecord
{
    protected static string $resource = WorkScheduleResource::class;
}
