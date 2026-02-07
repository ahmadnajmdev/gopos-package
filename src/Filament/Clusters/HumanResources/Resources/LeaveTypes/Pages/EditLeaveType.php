<?php

namespace Gopos\Filament\Clusters\HumanResources\Resources\LeaveTypes\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Gopos\Filament\Clusters\HumanResources\Resources\LeaveTypes\LeaveTypeResource;

class EditLeaveType extends EditRecord
{
    protected static string $resource = LeaveTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
