<?php

namespace Gopos\Filament\Clusters\Accounting\Resources\TaxCodes\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Gopos\Filament\Clusters\Accounting\Resources\TaxCodes\TaxCodeResource;

class ManageTaxCodes extends ManageRecords
{
    protected static string $resource = TaxCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
