<?php

namespace Gopos\Filament\Clusters\Inventory\Resources\Inventory\Pages;

use Filament\Resources\Pages\EditRecord;
use Gopos\Filament\Clusters\Inventory\Resources\Inventory\InventoryMovementResource;

class EditInventoryMovement extends EditRecord
{
    protected static string $resource = InventoryMovementResource::class;
}
