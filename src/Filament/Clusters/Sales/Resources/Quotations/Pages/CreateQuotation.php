<?php

namespace Gopos\Filament\Clusters\Sales\Resources\Quotations\Pages;

use Filament\Resources\Pages\CreateRecord;
use Gopos\Filament\Clusters\Sales\Resources\Quotations\QuotationResource;

class CreateQuotation extends CreateRecord
{
    protected static string $resource = QuotationResource::class;
}
