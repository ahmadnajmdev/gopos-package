<?php

namespace Gopos\Filament\Clusters\Purchases\Resources\PurchaseReturns\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Gopos\Filament\Clusters\Purchases\Resources\PurchaseReturns\PurchaseReturnResource;

class ListPurchaseReturns extends ListRecords
{
    protected static string $resource = PurchaseReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
