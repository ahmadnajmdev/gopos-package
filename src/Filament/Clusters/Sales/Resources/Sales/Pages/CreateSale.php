<?php

namespace Gopos\Filament\Clusters\Sales\Resources\Sales\Pages;

use Filament\Resources\Pages\CreateRecord;
use Gopos\Filament\Clusters\Sales\Resources\Sales\SaleResource;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;
}
