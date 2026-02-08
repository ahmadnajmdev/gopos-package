<?php

namespace Gopos\Filament\Clusters\Hr\Resources\Positions\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Gopos\Filament\Clusters\Hr\Resources\Positions\PositionResource;

class ListPositions extends ListRecords
{
    protected static string $resource = PositionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
