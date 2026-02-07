<?php

namespace Gopos\Filament\Clusters\Inventory\Resources\Units\Pages;

use Filament\Resources\Pages\CreateRecord;
use Gopos\Filament\Clusters\Inventory\Resources\Units\UnitResource;

class CreateUnit extends CreateRecord
{
    protected static string $resource = UnitResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
