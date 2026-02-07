<?php

namespace Gopos\Filament\Clusters\Accounting\Resources\Currencies\Pages;

use Filament\Resources\Pages\CreateRecord;
use Gopos\Filament\Clusters\Accounting\Resources\Currencies\CurrencyResource;

class CreateCurrency extends CreateRecord
{
    protected static string $resource = CurrencyResource::class;
}
