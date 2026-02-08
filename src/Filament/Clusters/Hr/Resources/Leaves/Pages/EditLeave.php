<?php

namespace Gopos\Filament\Clusters\Hr\Resources\Leaves\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Gopos\Filament\Clusters\Hr\Resources\Leaves\LeaveResource;

class EditLeave extends EditRecord
{
    protected static string $resource = LeaveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
