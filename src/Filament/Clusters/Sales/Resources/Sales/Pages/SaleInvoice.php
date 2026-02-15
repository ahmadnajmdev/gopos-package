<?php

namespace Gopos\Filament\Clusters\Sales\Resources\Sales\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Gopos\Enums\InstallmentStatus;
use Gopos\Filament\Clusters\Sales\Resources\Sales\SaleResource;
use Gopos\Models\Sale;
use Gopos\Models\SaleInstallment;
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
                        $sale = Sale::with(['items.product', 'customer', 'payments', 'posSession.user'])
                            ->find($this->sale->id);

                        $receiptService = app(ReceiptPrinterService::class);
                        $receiptData = $receiptService->generateReceipt($sale);

                        $this->dispatch('print-thermal-receipt', html: $receiptData['html']);
                    }),
            ])
                ->label(__('Print'))
                ->icon('heroicon-o-printer')
                ->button(),
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
                        ->maxValue($this->sale->total_amount - $this->sale->paid_amount),
                ])
                ->action(function (array $data): void {
                    $this->sale->paid_amount += $data['amount'];
                    $this->sale->save();
                    Notification::make()
                        ->title(__('Payment added successfully'))
                        ->success()
                        ->send();

                })->successRedirectUrl(SaleResource::getUrl('index')),
            Action::make('createInstallmentPlan')
                ->label(__('Create Installment Plan'))
                ->icon('heroicon-o-calendar-days')
                ->color('info')
                ->visible(
                    $this->sale->isInstallment()
                    && $this->sale->installments()->count() === 0
                    && $this->sale->customer_id !== null
                )
                ->schema([
                    TextInput::make('number_of_installments')
                        ->label(__('Number of Installments'))
                        ->required()
                        ->numeric()
                        ->minValue(2)
                        ->maxValue(60)
                        ->default(3),
                    DatePicker::make('first_due_date')
                        ->label(__('First Due Date'))
                        ->required()
                        ->default(now()->addMonth()),
                    Select::make('frequency')
                        ->label(__('Frequency'))
                        ->options([
                            'weekly' => __('Weekly'),
                            'biweekly' => __('Every 2 Weeks'),
                            'monthly' => __('Monthly'),
                        ])
                        ->default('monthly')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $remaining = (float) $this->sale->total_amount - (float) $this->sale->paid_amount;
                    $count = (int) $data['number_of_installments'];
                    $installmentAmount = round($remaining / $count, 2);
                    $dueDate = \Carbon\Carbon::parse($data['first_due_date']);

                    for ($i = 1; $i <= $count; $i++) {
                        $amount = ($i === $count)
                            ? $remaining - ($installmentAmount * ($count - 1))
                            : $installmentAmount;

                        SaleInstallment::create([
                            'sale_id' => $this->sale->id,
                            'installment_number' => $i,
                            'due_date' => $dueDate->copy(),
                            'amount' => $amount,
                            'status' => InstallmentStatus::Pending,
                        ]);

                        $dueDate = match ($data['frequency']) {
                            'weekly' => $dueDate->addWeek(),
                            'biweekly' => $dueDate->addWeeks(2),
                            default => $dueDate->addMonth(),
                        };
                    }

                    Notification::make()
                        ->title(__('Installment plan created successfully'))
                        ->success()
                        ->send();

                    $this->redirect(SaleResource::getUrl('invoice', ['record' => $this->sale]));
                }),
            Action::make('payInstallment')
                ->label(__('Pay Installment'))
                ->icon('heroicon-o-credit-card')
                ->color('warning')
                ->visible(
                    $this->sale->isInstallment()
                    && $this->sale->installments()
                        ->whereIn('status', [InstallmentStatus::Pending, InstallmentStatus::Overdue, InstallmentStatus::Partial])
                        ->exists()
                )
                ->schema([
                    Select::make('installment_id')
                        ->label(__('Installment'))
                        ->options(
                            $this->sale->installments()
                                ->whereIn('status', [InstallmentStatus::Pending, InstallmentStatus::Overdue, InstallmentStatus::Partial])
                                ->get()
                                ->mapWithKeys(fn ($inst) => [
                                    $inst->id => __('#:number - Due: :date - Remaining: :amount', [
                                        'number' => $inst->installment_number,
                                        'date' => $inst->due_date->format('Y-m-d'),
                                        'amount' => number_format($inst->remaining, 2),
                                    ]),
                                ])
                        )
                        ->required(),
                    TextInput::make('amount')
                        ->label(__('Payment Amount'))
                        ->required()
                        ->numeric()
                        ->minValue(0.01),
                ])
                ->action(function (array $data): void {
                    $installment = SaleInstallment::find($data['installment_id']);
                    $payAmount = min((float) $data['amount'], $installment->remaining);

                    $installment->paid_amount += $payAmount;

                    if ($installment->paid_amount >= $installment->amount) {
                        $installment->status = InstallmentStatus::Paid;
                        $installment->paid_date = now();
                    } else {
                        $installment->status = InstallmentStatus::Partial;
                    }

                    $installment->save();

                    $this->sale->paid_amount += $payAmount;
                    $this->sale->save();

                    Notification::make()
                        ->title(__('Installment payment recorded'))
                        ->success()
                        ->send();

                    $this->redirect(SaleResource::getUrl('invoice', ['record' => $this->sale]));
                }),
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
