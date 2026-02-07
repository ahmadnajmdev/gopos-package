<?php

namespace Gopos\Filament\Clusters\Inventory\Resources\Warehouses\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Gopos\Filament\Clusters\Inventory\Resources\Warehouses\WarehouseResource;

class ViewWarehouse extends ViewRecord
{
    protected static string $resource = WarehouseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
