<?php

namespace Gopos\Filament\Clusters\Purchases\Resources\Purchases\Pages;

use Filament\Resources\Pages\CreateRecord;
use Gopos\Filament\Clusters\Purchases\Resources\Purchases\PurchaseResource;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('invoice', ['record' => $this->record]);
    }
}
