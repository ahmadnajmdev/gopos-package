<?php

namespace Gopos\Filament\Clusters\Sales\Resources\Sales\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Gopos\Filament\Clusters\Sales\Resources\Sales\SaleResource;
use Gopos\Models\Sale;
use Gopos\Services\ReceiptPrinterService;

class SaleInvoice extends Page
{
    protected static string $resource = SaleResource::class;

    protected string $view = 'gopos::filament.resources.sale-resource.pages.sale-invoice';

    public $sale;

    public string $thermalReceiptHtml = '';

    protected function getHeaderActions(): array
    {

        return [
            ActionGroup::make([
                Action::make('printA4')
                    ->label(__('Print A4'))
                    ->icon('heroicon-o-document')
                    ->url(route('print-sale-invoice', [
                        'sale' => $this->sale->id,
                    ]))
                    ->openUrlInNewTab(),
                Action::make('printThermal')
                    ->label(__('Print Thermal (80mm)'))
                    ->icon('heroicon-o-receipt-percent')
                    ->action(function () {
                        // Load sale with relationships
                        $sale = Sale::with(['items.product', 'customer', 'payments', 'posSession.user'])
                            ->find($this->sale->id);

                        $receiptService = app(ReceiptPrinterService::class);
                        $receiptData = $receiptService->generateReceipt($sale);

                        // Dispatch browser event to trigger JavaScript
                        $this->dispatch('print-thermal-receipt', html: $receiptData['html']);
                    }),
            ])
                ->label(__('Print'))
                ->icon('heroicon-o-printer')
                ->button(),
            // Action to add payment amount
            Action::make('addPayment')
                ->label(__('Add Payment'))
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->visible($this->sale->total_amount > $this->sale->paid_amount)
                ->schema([
                    TextInput::make('amount')
                        ->label(__('Paid amount'))
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->maxValue($this->sale->total_amount - $this->sale->paid_amount)
                        ->minValue(0),
                ])
                ->action(function (array $data): void {
                    $this->sale->paid_amount += $data['amount'];
                    $this->sale->save();
                    Notification::make()
                        ->title(__('Payment added successfully'))
                        ->success()
                        ->send();

                })->successRedirectUrl($this->getResource()::getUrl('index')),
        ];
    }

    public function getTitle(): string
    {
        return __('Invoice').' #'.$this->sale->sale_number;
    }

    public function mount($record): void
    {
        $this->sale = Sale::query()->find($record);
    }
}
