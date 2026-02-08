<?php

namespace Gopos\Filament\Clusters\Hr\Resources\Holidays\Pages;

use Filament\Resources\Pages\CreateRecord;
use Gopos\Filament\Clusters\Hr\Resources\Holidays\HolidayResource;

class CreateHoliday extends CreateRecord
{
    protected static string $resource = HolidayResource::class;
}
