<?php

namespace Gopos\Filament\Clusters\Sales\Resources\Sales\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Gopos\Filament\Clusters\Sales\Resources\Sales\SaleResource;

class ViewSale extends ViewRecord
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_invoice')
                ->label(__('View Invoice'))
                ->color('success')
                ->url(fn () => SaleResource::getUrl('invoice', ['record' => $this->record->getKey()]))
                ->icon('heroicon-o-document-text'),
        ];
    }
}
