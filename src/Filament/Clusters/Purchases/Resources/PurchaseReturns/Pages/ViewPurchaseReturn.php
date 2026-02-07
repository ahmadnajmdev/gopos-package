<?php

namespace Gopos\Filament\Clusters\Purchases\Resources\PurchaseReturns\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Gopos\Filament\Clusters\Purchases\Resources\PurchaseReturns\PurchaseReturnResource;

class ViewPurchaseReturn extends ViewRecord
{
    protected static string $resource = PurchaseReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
