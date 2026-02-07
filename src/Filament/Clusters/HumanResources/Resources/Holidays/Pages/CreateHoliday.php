<?php

namespace Gopos\Filament\Clusters\HumanResources\Resources\Holidays\Pages;

use Filament\Resources\Pages\CreateRecord;
use Gopos\Filament\Clusters\HumanResources\Resources\Holidays\HolidayResource;

class CreateHoliday extends CreateRecord
{
    protected static string $resource = HolidayResource::class;
}
