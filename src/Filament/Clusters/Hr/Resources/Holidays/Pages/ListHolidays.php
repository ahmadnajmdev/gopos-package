<?php

namespace Gopos\Filament\Clusters\Hr\Resources\Holidays\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Gopos\Filament\Clusters\Hr\Resources\Holidays\HolidayResource;

class ListHolidays extends ListRecords
{
    protected static string $resource = HolidayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
