<?php

namespace Gopos\Filament\Clusters\Hr\Resources\Leaves\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Gopos\Filament\Clusters\Hr\Resources\Leaves\LeaveResource;

class ListLeaves extends ListRecords
{
    protected static string $resource = LeaveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
