<?php

namespace Gopos\Filament\Clusters\Inventory\Resources\StockTransfers\Pages;

use Filament\Resources\Pages\CreateRecord;
use Gopos\Filament\Clusters\Inventory\Resources\StockTransfers\StockTransferResource;

class CreateStockTransfer extends CreateRecord
{
    protected static string $resource = StockTransferResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
