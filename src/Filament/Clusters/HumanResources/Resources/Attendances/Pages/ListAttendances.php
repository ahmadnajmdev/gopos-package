<?php

namespace Gopos\Filament\Clusters\HumanResources\Resources\Attendances\Pages;

use Filament\Resources\Pages\ListRecords;
use Gopos\Filament\Clusters\HumanResources\Resources\Attendances\AttendanceResource;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;
}
