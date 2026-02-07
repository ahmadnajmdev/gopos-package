<?php

namespace Gopos\Filament\Clusters\Purchases\Resources\Purchases\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Gopos\Filament\Clusters\Purchases\Resources\Purchases\PurchaseResource;

class ViewPurchase extends ViewRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
