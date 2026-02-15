<?php

namespace Gopos\Filament\Clusters\Sales\Resources\Quotations\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Gopos\Filament\Clusters\Sales\Resources\Quotations\QuotationResource;
use Gopos\Filament\Clusters\Sales\Resources\Sales\SaleResource;

class ViewQuotation extends ViewRecord
{
    protected static string $resource = QuotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('convert_to_sale')
                ->label(__('Convert to Sale'))
                ->icon('heroicon-o-arrow-right-circle')
                ->color('success')
                ->visible(fn () => $this->record->isConvertible())
                ->requiresConfirmation()
                ->modalHeading(__('Convert Quotation to Sale'))
                ->modalDescription(__('This will create a new sale from this quotation and mark it as accepted.'))
                ->action(function () {
                    $sale = QuotationResource::convertToSale($this->record);

                    Notification::make()
                        ->title(__('Quotation converted to sale successfully'))
                        ->success()
                        ->send();

                    return redirect(SaleResource::getUrl('invoice', ['record' => $sale]));
                }),
            EditAction::make(),
        ];
    }
}
