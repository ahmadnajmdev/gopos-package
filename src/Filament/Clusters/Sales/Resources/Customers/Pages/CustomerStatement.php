<?php

namespace Gopos\Filament\Clusters\Sales\Resources\Customers\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Contracts\HasForms;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Gopos\Filament\Clusters\Sales\Resources\Customers\CustomerResource;
use Gopos\Models\Currency;
use Gopos\Models\Sale;
use Illuminate\Database\Eloquent\Builder;

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
            // If already in base currency, just use paid_amount
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
            Action::make('print')
                ->label(__('Print Statement'))
                ->icon('heroicon-o-printer')
                ->action('printStatement'),
        ];
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
