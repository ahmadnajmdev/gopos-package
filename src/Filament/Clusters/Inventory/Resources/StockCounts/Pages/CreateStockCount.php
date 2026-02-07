<?php

namespace Gopos\Filament\Clusters\Inventory\Resources\StockCounts\Pages;

use Filament\Resources\Pages\CreateRecord;
use Gopos\Filament\Clusters\Inventory\Resources\StockCounts\StockCountResource;
use Gopos\Services\InventoryService;

class CreateStockCount extends CreateRecord
{
    protected static string $resource = StockCountResource::class;

    protected function afterCreate(): void
    {
        // Initialize stock count items
        $inventoryService = app(InventoryService::class);
        $inventoryService->initializeStockCount($this->record);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
