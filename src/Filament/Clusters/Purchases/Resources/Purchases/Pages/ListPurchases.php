<?php

namespace Gopos\Filament\Clusters\Purchases\Resources\Purchases\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Gopos\Filament\Clusters\Purchases\Resources\Purchases\PurchaseResource;

class ListPurchases extends ListRecords
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label(__('Add Purchase')),
        ];
    }
}
