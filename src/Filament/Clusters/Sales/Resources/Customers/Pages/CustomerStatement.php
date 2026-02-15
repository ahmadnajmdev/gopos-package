<?php

namespace Gopos\Filament\Clusters\Sales\Resources\Customers\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Gopos\Enums\PaymentMethod;
use Gopos\Filament\Clusters\Sales\Resources\Customers\CustomerResource;
use Gopos\Models\Currency;
use Gopos\Models\Sale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CustomerStatement extends Page implements HasForms, HasTable
{
    use InteractsWithRecord, InteractsWithTable;

    protected static string $resource = CustomerResource::class;

    protected string $view = 'gopos::filament.resources.customers.pages.customer-statement';

    protected static ?string $title = 'Customer Statement';

    protected static ?string $navigationLabel = 'Statement';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    public static function getNavigationLabel(): string
    {
        return __('Customer Statement');
    }

    public function getTitle(): string
    {
        return __('Customer Statement');
    }

    public ?string $fromDate = null;

    public ?string $toDate = null;

    public ?string $status = null;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('sale_number')
                    ->label(__('Invoice #'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sale_date')
                    ->label(__('Date'))
                    ->date('d-m-Y')
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label(__('Total Amount'))
                    ->numeric(locale: 'en')
                    ->suffix(fn ($record) => ' '.($record->currency?->symbol ?? ''))
                    ->sortable(),
                TextColumn::make('paid_amount')
                    ->label(__('Paid Amount'))
                    ->numeric(locale: 'en')
                    ->suffix(fn ($record) => ' '.($record->currency?->symbol ?? ''))
                    ->sortable(),
                TextColumn::make('balance')
                    ->label(__('Balance'))
                    ->numeric(locale: 'en')
                    ->suffix(fn ($record) => ' '.($record->currency?->symbol ?? ''))
                    ->getStateUsing(fn (Sale $record): float => $record->total_amount - $record->paid_amount)
                    ->color(fn (Sale $record): string => $record->total_amount - $record->paid_amount > 0 ? 'danger' : 'success'
                    ),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->getStateUsing(fn (Sale $record): string => $record->paid_amount == 0 ? __('Unpaid') :
                        ($record->paid_amount >= $record->total_amount ? __('Paid') : __('Partially Paid'))
                    )
                    ->color(fn (Sale $record): string => $record->paid_amount == 0 ? 'danger' :
                        ($record->paid_amount >= $record->total_amount ? 'success' : 'warning')
                    ),
            ])
            ->recordActions([
                Action::make('pay')
                    ->label(__('Pay'))
                    ->icon(Heroicon::OutlinedBanknotes)
                    ->color('success')
                    ->visible(fn (Sale $record): bool => $record->total_amount - $record->paid_amount > 0)
                    ->fillForm(fn (Sale $record): array => [
                        'amount' => round($record->total_amount - $record->paid_amount, 2),
                        'currency_id' => $record->currency_id,
                    ])
                    ->schema([
                        TextInput::make('amount')
                            ->label(__('Amount'))
                            ->required()
                            ->numeric()
                            ->minValue(0.01),
                        Select::make('payment_method')
                            ->label(__('Payment Method'))
                            ->required()
                            ->options(collect(PaymentMethod::cases())
                                ->filter(fn (PaymentMethod $method) => $method !== PaymentMethod::Credit)
                                ->mapWithKeys(fn (PaymentMethod $method) => [$method->value => $method->getLabel()])
                                ->toArray()),
                        Select::make('currency_id')
                            ->label(__('Currency'))
                            ->options(Currency::pluck('name', 'id'))
                            ->required(),
                        TextInput::make('reference_number')
                            ->label(__('Reference Number'))
                            ->maxLength(255),
                        Textarea::make('notes')
                            ->label(__('Notes'))
                            ->rows(2),
                    ])
                    ->action(function (Sale $record, array $data): void {
                        $this->processPayment($record, $data);
                    }),
            ])
            ->filters([
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('from_date')
                            ->label(__('From Date'))
                            ->live(),
                        DatePicker::make('to_date')
                            ->label(__('To Date'))
                            ->live(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('sale_date', '>=', $date),
                            )
                            ->when(
                                $data['to_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('sale_date', '<=', $date),
                            );
                    }),
            ])
            ->defaultSort('sale_date', 'desc');
    }

    protected function getTableQuery(): Builder
    {
        $query = $this->getRecord()->sales()->getQuery();

        return $query;
    }

    public function getCustomerSummary(): array
    {
        $customer = $this->getRecord();
        $sales = $this->getTableQuery()->get();

        $totalSales = $sales->sum('amount_in_base_currency');
        $totalPaid = $sales->sum(function ($sale) {
            if ($sale->currency_id == Currency::getBaseCurrency()->id) {
                return $sale->paid_amount;
            }

            return $sale->currency->convertFromCurrency($sale->paid_amount, $sale->currency->code);
        });
        $totalBalance = $totalSales - $totalPaid;

        return [
            'customer' => $customer,
            'total_sales' => $totalSales,
            'total_paid' => $totalPaid,
            'total_balance' => $totalBalance,
            'total_invoices' => $sales->count(),
            'paid_invoices' => $sales->where('paid_amount', '>=', 'total_amount')->count(),
            'unpaid_invoices' => $sales->where('paid_amount', 0)->count(),
            'partial_invoices' => $sales->where('paid_amount', '>', 0)->where('paid_amount', '<', 'total_amount')->count(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('payDebit')
                ->label(__('Pay Debit'))
                ->icon(Heroicon::OutlinedBanknotes)
                ->color('success')
                ->visible(fn (): bool => $this->getCustomerTotalBalance() > 0)
                ->fillForm(fn (): array => [
                    'amount' => round($this->getCustomerTotalBalance(), 2),
                    'currency_id' => Currency::getBaseCurrency()?->id,
                ])
                ->schema([
                    TextInput::make('amount')
                        ->label(__('Amount'))
                        ->required()
                        ->numeric()
                        ->minValue(0.01)
                        ->maxValue(fn (): float => round($this->getCustomerTotalBalance(), 2))
                        ->helperText(fn (): string => __('Total outstanding balance: :amount :currency', [
                            'amount' => number_format($this->getCustomerTotalBalance(), 2),
                            'currency' => Currency::getBaseCurrency()?->symbol ?? '',
                        ])),
                    Select::make('payment_method')
                        ->label(__('Payment Method'))
                        ->required()
                        ->options(collect(PaymentMethod::cases())
                            ->filter(fn (PaymentMethod $method) => $method !== PaymentMethod::Credit)
                            ->mapWithKeys(fn (PaymentMethod $method) => [$method->value => $method->getLabel()])
                            ->toArray()),
                    Select::make('currency_id')
                        ->label(__('Currency'))
                        ->options(Currency::pluck('name', 'id'))
                        ->required(),
                    TextInput::make('reference_number')
                        ->label(__('Reference Number'))
                        ->maxLength(255),
                    Textarea::make('notes')
                        ->label(__('Notes'))
                        ->rows(2),
                ])
                ->action(function (array $data): void {
                    $this->processPayDebit($data);
                }),
            Action::make('print')
                ->label(__('Print Statement'))
                ->icon('heroicon-o-printer')
                ->action('printStatement'),
        ];
    }

    protected function processPayment(Sale $sale, array $data): void
    {
        $remaining = round($sale->total_amount - $sale->paid_amount, 2);
        $amount = (float) $data['amount'];

        if ($amount > $remaining) {
            Notification::make()
                ->title(__('Payment amount exceeds the remaining balance'))
                ->danger()
                ->send();

            return;
        }

        $currency = Currency::find($data['currency_id']);
        $exchangeRate = $currency?->exchange_rate ?? 1;
        $baseCurrency = Currency::getBaseCurrency();
        $amountInBase = $baseCurrency
            ? $currency->convertFromCurrency($amount, $currency->code)
            : $amount;

        DB::transaction(function () use ($sale, $data, $amount, $exchangeRate, $amountInBase) {
            $sale->payments()->create([
                'payment_method' => $data['payment_method'],
                'amount' => $amount,
                'currency_id' => $data['currency_id'],
                'exchange_rate' => $exchangeRate,
                'amount_in_base_currency' => $amountInBase,
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $newPaidAmount = $sale->paid_amount + $amount;
            $status = $newPaidAmount >= $sale->total_amount ? 'paid' : 'partial';

            $sale->update([
                'paid_amount' => $newPaidAmount,
                'status' => $status,
            ]);
        });

        Notification::make()
            ->title(__('Payment recorded successfully'))
            ->body(__(':amount :currency paid on invoice #:invoice', [
                'amount' => number_format($amount, 2),
                'currency' => $currency?->symbol ?? '',
                'invoice' => $sale->sale_number,
            ]))
            ->success()
            ->send();
    }

    protected function processPayDebit(array $data): void
    {
        $currency = Currency::find($data['currency_id']);
        $baseCurrency = Currency::getBaseCurrency();
        $totalAmount = (float) $data['amount'];
        $remaining = $totalAmount;

        $unpaidSales = $this->getRecord()
            ->sales()
            ->whereColumn('paid_amount', '<', 'total_amount')
            ->orderBy('sale_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        if ($unpaidSales->isEmpty()) {
            Notification::make()
                ->title(__('No unpaid invoices found'))
                ->warning()
                ->send();

            return;
        }

        DB::transaction(function () use ($unpaidSales, $data, $currency, $baseCurrency, &$remaining) {
            foreach ($unpaidSales as $sale) {
                if ($remaining <= 0) {
                    break;
                }

                $saleBalance = round($sale->total_amount - $sale->paid_amount, 2);

                if ($sale->currency_id !== (int) $data['currency_id']) {
                    $saleBalanceInPaymentCurrency = $baseCurrency && $currency
                        ? round($sale->amount_in_base_currency * ($currency->exchange_rate / $baseCurrency->exchange_rate) - $sale->paid_amount, 2)
                        : $saleBalance;
                    $payAmount = min($remaining, max($saleBalanceInPaymentCurrency, 0));
                    $payAmountInSaleCurrency = $sale->currency
                        ? round($payAmount * ($sale->currency->exchange_rate / ($currency->exchange_rate ?: 1)), 2)
                        : $payAmount;
                } else {
                    $payAmount = min($remaining, $saleBalance);
                    $payAmountInSaleCurrency = $payAmount;
                }

                if ($payAmount <= 0) {
                    continue;
                }

                $exchangeRate = $currency?->exchange_rate ?? 1;
                $amountInBase = $baseCurrency
                    ? $currency->convertFromCurrency($payAmount, $currency->code)
                    : $payAmount;

                $sale->payments()->create([
                    'payment_method' => $data['payment_method'],
                    'amount' => $payAmountInSaleCurrency,
                    'currency_id' => $data['currency_id'],
                    'exchange_rate' => $exchangeRate,
                    'amount_in_base_currency' => $amountInBase,
                    'reference_number' => $data['reference_number'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ]);

                $newPaidAmount = $sale->paid_amount + $payAmountInSaleCurrency;
                $status = $newPaidAmount >= $sale->total_amount ? 'paid' : 'partial';

                $sale->update([
                    'paid_amount' => $newPaidAmount,
                    'status' => $status,
                ]);

                $remaining = round($remaining - $payAmount, 2);
            }
        });

        $paidAmount = round($totalAmount - $remaining, 2);

        Notification::make()
            ->title(__('Debit payment recorded successfully'))
            ->body(__(':amount :currency applied to outstanding invoices', [
                'amount' => number_format($paidAmount, 2),
                'currency' => $currency?->symbol ?? '',
            ]))
            ->success()
            ->send();
    }

    protected function getCustomerTotalBalance(): float
    {
        $summary = $this->getCustomerSummary();

        return (float) $summary['total_balance'];
    }

    public function printStatement(): void
    {
        $customer = $this->getRecord();
        $summary = $this->getCustomerSummary();
        $sales = $this->getTableQuery()->get();

        $printUrl = route('customer.statement.print', [
            'customer' => $customer->id,
            'from_date' => $this->fromDate,
            'to_date' => $this->toDate,
            'status' => $this->status,
        ]);

        $this->redirect($printUrl);
    }
}
