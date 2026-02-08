<?php

namespace Gopos\Filament\Clusters\Sales\Resources\Sales\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Gopos\Filament\Clusters\Sales\Resources\Sales\SaleResource;
use Gopos\Filament\Pages\Pos;

class ListSales extends ListRecords
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('POS')
                ->url(Pos::getUrl())
                ->icon('heroicon-o-shopping-cart')
                ->color('success')
                ->label(__('POS')),
        ];
    }
}
