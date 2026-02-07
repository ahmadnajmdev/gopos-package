<?php

namespace Gopos\Filament\Clusters\Inventory\Resources\Products\Pages;

use Filament\Resources\Pages\CreateRecord;
use Gopos\Filament\Clusters\Inventory\Resources\Products\ProductResource;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
