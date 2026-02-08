<?php

namespace Gopos\Filament\Clusters\Hr\Resources\LeaveTypes\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Gopos\Filament\Clusters\Hr\Resources\LeaveTypes\LeaveTypeResource;

class ManageLeaveTypes extends ManageRecords
{
    protected static string $resource = LeaveTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
