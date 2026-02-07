<?php

namespace Gopos\Filament\Clusters\HumanResources\Resources\LeaveRequests\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Gopos\Filament\Clusters\HumanResources\Resources\LeaveRequests\LeaveRequestResource;

class ListLeaveRequests extends ListRecords
{
    protected static string $resource = LeaveRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
