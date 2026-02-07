<?php

namespace Gopos\Filament\Clusters\HumanResources\Resources\LeaveTypes\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Gopos\Filament\Clusters\HumanResources\Resources\LeaveTypes\LeaveTypeResource;

class ListLeaveTypes extends ListRecords
{
    protected static string $resource = LeaveTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
