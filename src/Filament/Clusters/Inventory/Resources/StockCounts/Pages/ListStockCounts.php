<?php

namespace Gopos\Filament\Clusters\Inventory\Resources\StockCounts\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Gopos\Filament\Clusters\Inventory\Resources\StockCounts\StockCountResource;
use Illuminate\Database\Eloquent\Builder;

class ListStockCounts extends ListRecords
{
    protected static string $resource = StockCountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function modifyQueryUsing(Builder $query): Builder
    {
        return $query->with(['warehouse', 'creator', 'completer', 'items']);
    }
}
