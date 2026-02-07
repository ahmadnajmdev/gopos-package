<?php

namespace Gopos\Filament\Clusters\Inventory\Resources\StockTransfers\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Gopos\Filament\Clusters\Inventory\Resources\StockTransfers\StockTransferResource;
use Illuminate\Database\Eloquent\Builder;

class ListStockTransfers extends ListRecords
{
    protected static string $resource = StockTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function modifyQueryUsing(Builder $query): Builder
    {
        return $query->with(['fromWarehouse', 'toWarehouse', 'creator', 'approver', 'receiver', 'items']);
    }
}
