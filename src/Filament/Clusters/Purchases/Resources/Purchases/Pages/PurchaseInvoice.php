<?php

namespace Gopos\Filament\Clusters\Purchases\Resources\Purchases\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Gopos\Filament\Clusters\Purchases\Resources\Purchases\PurchaseResource;
use Gopos\Models\Purchase;

class PurchaseInvoice extends Page
{
    protected static string $resource = PurchaseResource::class;

    protected string $view = 'gopos::filament.resources.purchase-resource.pages.purchase-invoice';

    public $purchase;

    public function getTitle(): string
    {
        return __('Invoice').' #'.$this->purchase->purchase_number;
    }

    protected function getHeaderActions(): array
    {

        return [
            Action::make('print')
                ->label(__('Print'))
                ->icon('heroicon-o-printer')
                ->url(route('print-invoice', [
                    'purchase' => $this->purchase->id,
                ]))
                ->openUrlInNewTab(),
            // Action to add payment amount
            Action::make('addPayment')
                ->label(__('Add Payment'))
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->visible($this->purchase->total_amount > $this->purchase->paid_amount)
                ->schema([
                    TextInput::make('amount')
                        ->label(__('Paid amount'))
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->maxValue($this->purchase->total_amount - $this->purchase->paid_amount)
                        ->minValue(0),
                ])
                ->action(function (array $data): void {
                    $this->purchase->paid_amount += $data['amount'];
                    $this->purchase->save();
                    Notification::make()
                        ->title(__('Payment added successfully'))
                        ->success()
                        ->send();
                    // update ui
                    $this->dispatch('refresh');
                }),
        ];
    }

    public function mount($record): void
    {
        $this->purchase = Purchase::query()->find($record);
    }
}
