<?php

namespace Gopos\Filament\Clusters\Purchases\Resources\Purchases;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Gopos\Filament\Clusters\Purchases\PurchasesCluster;
use Gopos\Filament\Clusters\Purchases\Resources\PurchaseReturns\PurchaseReturnResource;
use Gopos\Filament\Clusters\Purchases\Resources\Purchases\Pages\CreatePurchase;
use Gopos\Filament\Clusters\Purchases\Resources\Purchases\Pages\EditPurchase;
use Gopos\Filament\Clusters\Purchases\Resources\Purchases\Pages\ListPurchases;
use Gopos\Filament\Clusters\Purchases\Resources\Purchases\Pages\PurchaseInvoice;
use Gopos\Filament\Clusters\Purchases\Resources\Purchases\Pages\ViewPurchase;
use Gopos\Filament\Clusters\Purchases\Resources\Suppliers\SupplierResource;
use Gopos\Models\Currency;
use Gopos\Models\Product;
use Gopos\Models\Purchase;
use Illuminate\Database\Eloquent\Builder;

class PurchaseResource extends Resource
{
    protected static ?string $cluster = PurchasesCluster::class;

    protected static ?string $model = Purchase::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('Purchases');
    }

    /**
     * Convert amount from base currency to selected currency
     */
    private static function convertFromBaseCurrency(float $amount, int $currencyId): float
    {
        $baseCurrency = Currency::getBaseCurrency();
        $targetCurrency = Currency::find($currencyId);

        if (! $baseCurrency || ! $targetCurrency || ! $baseCurrency->exchange_rate || ! $targetCurrency->exchange_rate) {
            return self::roundMoney($amount);
        }

        // Convert from base currency to target currency
        return self::roundMoney($amount * $targetCurrency->exchange_rate / $baseCurrency->exchange_rate);
    }

    /**
     * Convert amount from selected currency to base currency
     */
    private static function convertToBaseCurrency(float $amount, int $currencyId): float
    {
        $baseCurrency = Currency::getBaseCurrency();
        $sourceCurrency = Currency::find($currencyId);

        if (! $baseCurrency || ! $sourceCurrency || ! $baseCurrency->exchange_rate || ! $sourceCurrency->exchange_rate) {
            return self::roundMoney($amount);
        }

        // Convert from source currency to base currency
        return self::roundMoney($amount * $baseCurrency->exchange_rate / $sourceCurrency->exchange_rate);
    }

    /**
     * Round monetary values to 2 decimal places
     */
    private static function roundMoney(float $amount): float
    {
        return round($amount, 2);
    }

    private static function calculateProductTotal(Set $set, Get $get): void
    {
        $product = Product::query()->find($get('product_id'));
        $baseCost = $product?->cost ?? 0.00; // Product cost is always in base currency
        $stock = (float) ($get('stock') ?? 0.00);
        $currencyId = $get('../../currency_id');

        if (! $currencyId) {
            return;
        }

        // Convert product cost from base currency to selected currency for display
        $convertedCost = self::convertFromBaseCurrency($baseCost, $currencyId);
        $totalAmount = self::roundMoney($stock * $convertedCost);

        $set('cost', self::roundMoney($convertedCost));
        $set('total_amount', $totalAmount);

        // Recalculate subtotal and total amount
        self::recalculateAllTotals($set, $get);
    }

    private static function recalculateAllTotals(Set $set, Get $get): void
    {
        // Detect context: if we're inside the repeater item, fields are 2 levels up
        $contextPrefix = $get('../../currency_id') !== null ? '../../' : '';

        $products = $get($contextPrefix.'products') ?? [];
        $subTotal = 0.00;

        foreach ($products as $product) {
            $subTotal += (float) ($product['total_amount'] ?? 0.00);
        }

        // Calculate total amount (subtotal - discount)
        $discount = (float) ($get($contextPrefix.'discount_amount') ?? 0.00);
        $total = self::roundMoney($subTotal) - self::roundMoney($discount);

        $set($contextPrefix.'sub_total', self::roundMoney($subTotal));
        $set($contextPrefix.'total_amount', max(0, self::roundMoney($total)));
        $set($contextPrefix.'paid_amount', max(0, self::roundMoney($total)));
    }

    private static function handleCurrencyChange(Set $set, Get $get): void
    {
        $currencyId = $get('currency_id');
        if (! $currencyId) {
            return;
        }

        // Recalculate all product costs and totals when currency changes
        $products = $get('products') ?? [];

        foreach ($products as $index => $productData) {
            if (! empty($productData['product_id'])) {
                $product = Product::query()->find($productData['product_id']);
                $baseCost = $product?->cost ?? 0.00;
                $stock = (float) ($productData['stock'] ?? 0.00);

                // Convert cost to new currency
                $convertedCost = self::convertFromBaseCurrency($baseCost, $currencyId);
                $totalAmount = self::roundMoney($stock * $convertedCost);

                $set("products.{$index}.cost", self::roundMoney($convertedCost));
                $set("products.{$index}.total_amount", $totalAmount);
            }
        }

        // Reset discount and paid amounts when currency changes (as they should be re-entered in new currency)
        $set('discount_amount', 0.00);
        $set('paid_amount', 0.00);

        // Recalculate totals
        self::recalculateAllTotals($set, $get);
    }

    private static function calculateTotalAmount(Set $set, Get $get): void
    {
        $subTotal = (float) ($get('sub_total') ?? 0.00);
        $discount = (float) ($get('discount_amount') ?? 0.00);
        $total = self::roundMoney($subTotal) - self::roundMoney($discount);

        $set('total_amount', max(0, self::roundMoney($total)));
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Purchase Details'))
                    ->schema([
                        TextInput::make('purchase_number')
                            ->required()
                            ->default(fn () => Purchase::generatePurchaseNumber())
                            ->readOnly()
                            ->maxLength(255),
                        Select::make('supplier_id')
                            ->relationship('supplier', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm(fn (Schema $schema) => SupplierResource::form($schema)),
                        DatePicker::make('purchase_date')
                            ->default(now())
                            ->required()
                            ->maxDate(now()->addDays(30)),
                        Select::make('warehouse_id')
                            ->relationship('warehouse', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('currency_id')
                            ->relationship('currency', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->default(fn () => Currency::getBaseCurrency()?->id)
                            ->live()
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::handleCurrencyChange($set, $get)),
                    ])
                    ->columns(5)
                    ->columnSpanFull(),

                Section::make(__('Products'))
                    ->schema([
                        Repeater::make('products')
                            ->table([
                                TableColumn::make(__('Product')),
                                TableColumn::make(__('Stock')),
                                TableColumn::make(__('Cost')),
                                TableColumn::make(__('Total Amount')),
                            ])
                            ->compact()
                            ->relationship('items')
                            ->live(onBlur: true)
                            ->defaultItems(0)
                            ->minItems(1)
                            ->addActionLabel(__('Add Product'))
                            ->schema([
                                Select::make('product_id')
                                    ->relationship('product', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateProductTotal($set, $get)),

                                TextInput::make('stock')
                                    ->suffix(function (Get $get) {
                                        $product = Product::query()->find($get('product_id'));

                                        return $product?->unit?->abbreviation ?? '';
                                    })
                                    ->numeric()
                                    ->default(1.00)
                                    ->minValue(0.01)
                                    ->live(debounce: 400)
                                    ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateProductTotal($set, $get))
                                    ->required(),

                                TextInput::make('cost')
                                    ->label(__('Cost (Converted)'))
                                    ->required()
                                    ->readOnly()
                                    ->numeric()
                                    ->default(0.00)
                                    ->prefix(function (Get $get) {
                                        $currencyId = $get('../../currency_id');
                                        $currency = Currency::find($currencyId);

                                        return $currency?->symbol ?? '';
                                    }),

                                TextInput::make('total_amount')
                                    ->required()
                                    ->readOnly()
                                    ->numeric()
                                    ->prefix(function (Get $get) {
                                        $currencyId = $get('../../currency_id');
                                        $currency = Currency::find($currencyId);

                                        return $currency?->symbol ?? '';
                                    }),
                            ])->columns(4),
                    ])->columnSpanFull(),

                Section::make(__('Purchase Payment'))
                    ->schema([
                        TextInput::make('sub_total')
                            ->label(__('Subtotal'))
                            ->required()
                            ->readOnly()
                            ->numeric()
                            ->prefix(function (Get $get) {
                                $currencyId = $get('currency_id');
                                $currency = Currency::find($currencyId);

                                return $currency?->symbol ?? '';
                            }),

                        TextInput::make('discount_amount')
                            ->label(__('Discount Amount'))
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->live(debounce: 400)
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateTotalAmount($set, $get))
                            ->maxValue(fn (Get $get) => $get('sub_total') ?? 0)
                            ->default(0.00)
                            ->prefix(function (Get $get) {
                                $currencyId = $get('currency_id');
                                $currency = Currency::find($currencyId);

                                return $currency?->symbol ?? '';
                            }),

                        TextInput::make('total_amount')
                            ->label(__('Total Amount'))
                            ->required()
                            ->readOnly()
                            ->numeric()
                            ->prefix(function (Get $get) {
                                $currencyId = $get('currency_id');
                                $currency = Currency::find($currencyId);

                                return $currency?->symbol ?? '';
                            }),

                        TextInput::make('paid_amount')
                            ->label(__('Paid Amount'))
                            ->required()
                            ->numeric()
                            ->live(debounce: 400)
                            ->minValue(0)
                            ->maxValue(fn (Get $get) => $get('total_amount') ?? 0)
                            ->default(0.00)
                            ->prefix(function (Get $get) {
                                $currencyId = $get('currency_id');
                                $currency = Currency::find($currencyId);

                                return $currency?->symbol ?? '';
                            }),
                    ])->columns(4)
                    ->columnSpanFull(),
                Section::make(__('Additional Information'))
                    ->schema([
                        Textarea::make('notes')
                            ->label(__('Notes'))
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull(),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('purchase_number')
                    ->label(__('Purchase #'))
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold),
                TextColumn::make('supplier.name')
                    ->label(__('Supplier'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('warehouse.name')
                    ->label(__('Warehouse'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('purchase_date')
                    ->label(__('Date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('items_count')
                    ->label(__('Items'))
                    ->counts('items')
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label(__('Total'))
                    ->numeric(locale: 'en')
                    ->suffix(fn ($record) => ' '.$record->currency?->symbol)
                    ->sortable()
                    ->weight(FontWeight::Bold),
                TextColumn::make('paid_amount')
                    ->label(__('Paid'))
                    ->numeric(locale: 'en')
                    ->suffix(fn ($record) => ' '.$record->currency?->symbol)
                    ->sortable(),
                TextColumn::make('payment_status')
                    ->label(__('Status'))
                    ->getStateUsing(fn ($record) => match (true) {
                        $record->paid_amount >= $record->total_amount => __('Paid'),
                        $record->paid_amount > 0 => __('Partial'),
                        default => __('Unpaid'),
                    })
                    ->badge()
                    ->color(fn ($record) => match (true) {
                        $record->paid_amount >= $record->total_amount => 'success',
                        $record->paid_amount > 0 => 'warning',
                        default => 'danger',
                    }),
                TextColumn::make('currency.name')
                    ->label(__('Currency'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('supplier_id')
                    ->label(__('Supplier'))
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('currency_id')
                    ->label(__('Currency'))
                    ->relationship('currency', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('warehouse_id')
                    ->label(__('Warehouse'))
                    ->relationship('warehouse', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('unpaid')
                    ->label(__('Unpaid/Partial'))
                    ->query(fn (Builder $query): Builder => $query->whereColumn('paid_amount', '<', 'total_amount'))
                    ->toggle(),
                Filter::make('today')
                    ->label(__('Today'))
                    ->query(fn (Builder $query): Builder => $query->whereDate('purchase_date', today()))
                    ->toggle(),
                QueryBuilder::make()
                    ->constraints([
                        TextConstraint::make('purchase_number')
                            ->label(__('Purchase Number')),
                        RelationshipConstraint::make('supplier')
                            ->label(__('Supplier'))
                            ->relationship('supplier', 'name'),
                        RelationshipConstraint::make('currency')
                            ->label(__('Currency'))
                            ->relationship('currency', 'name'),
                        NumberConstraint::make('sub_total')
                            ->label(__('Subtotal')),
                        NumberConstraint::make('discount_amount')
                            ->label(__('Discount')),
                        NumberConstraint::make('total_amount')
                            ->label(__('Total Amount')),
                        NumberConstraint::make('paid_amount')
                            ->label(__('Paid Amount')),
                        DateConstraint::make('purchase_date')
                            ->label(__('Purchase Date')),
                        DateConstraint::make('created_at')
                            ->label(__('Created at')),
                    ]),
            ])
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                Action::make('view_invoice')
                    ->label(__('View Invoice'))
                    ->icon('heroicon-o-document')
                    ->color('success')
                    ->url(function ($record) {
                        return self::getUrl('invoice', ['record' => $record]);
                    }),
                ViewAction::make(),
                Action::make('create_return')
                    ->label(__('Return'))
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->url(fn ($record) => PurchaseReturnResource::getUrl('create', parameters: [
                        'purchase_id' => $record->id,
                    ])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('purchase_date', 'desc');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Purchase Information'))
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('purchase_number')
                                    ->label(__('Purchase Number'))
                                    ->weight(FontWeight::Bold)
                                    ->copyable()
                                    ->copyMessage(__('Copied!')),
                                TextEntry::make('purchase_date')
                                    ->label(__('Purchase Date'))
                                    ->date(),
                                TextEntry::make('items_count')
                                    ->label(__('Total Items'))
                                    ->getStateUsing(fn ($record) => $record->items->count()),
                                TextEntry::make('payment_status')
                                    ->label(__('Payment Status'))
                                    ->getStateUsing(fn ($record) => match (true) {
                                        $record->paid_amount >= $record->total_amount => __('Paid'),
                                        $record->paid_amount > 0 => __('Partially Paid'),
                                        default => __('Unpaid'),
                                    })
                                    ->badge()
                                    ->color(fn ($record) => match (true) {
                                        $record->paid_amount >= $record->total_amount => 'success',
                                        $record->paid_amount > 0 => 'warning',
                                        default => 'danger',
                                    }),
                            ]),
                    ]),

                Section::make(__('Supplier Information'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('supplier.name')
                                    ->label(__('Supplier Name'))
                                    ->icon('heroicon-o-building-storefront'),
                                TextEntry::make('supplier.phone')
                                    ->label(__('Phone'))
                                    ->placeholder('-')
                                    ->icon('heroicon-o-phone'),
                                TextEntry::make('supplier.email')
                                    ->label(__('Email'))
                                    ->placeholder('-')
                                    ->icon('heroicon-o-envelope'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('warehouse.name')
                                    ->label(__('Warehouse'))
                                    ->placeholder('-')
                                    ->icon('heroicon-o-building-office'),
                                TextEntry::make('currency.name')
                                    ->label(__('Currency'))
                                    ->badge()
                                    ->color('info'),
                                TextEntry::make('exchange_rate')
                                    ->label(__('Exchange Rate'))
                                    ->numeric(locale: 'en', decimalPlaces: 4),
                            ]),
                    ])
                    ->collapsible(),

                Section::make(__('Purchase Items'))
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                TextEntry::make('product.name')
                                    ->label(__('Product'))
                                    ->weight(FontWeight::Medium),
                                TextEntry::make('stock')
                                    ->label(__('Qty'))
                                    ->numeric(locale: 'en'),
                                TextEntry::make('cost')
                                    ->label(__('Unit Cost'))
                                    ->numeric(locale: 'en')
                                    ->suffix(fn ($record) => ' '.($record->purchase?->currency?->symbol ?? '')),
                                TextEntry::make('total_amount')
                                    ->label(__('Total'))
                                    ->numeric(locale: 'en')
                                    ->suffix(fn ($record) => ' '.($record->purchase?->currency?->symbol ?? ''))
                                    ->weight(FontWeight::SemiBold),
                            ])
                            ->columns(4),
                    ]),

                Section::make(__('Payment Summary'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Grid::make(1)
                                    ->schema([
                                        TextEntry::make('sub_total')
                                            ->label(__('Subtotal'))
                                            ->numeric(locale: 'en')
                                            ->suffix(fn ($record) => ' '.($record->currency?->symbol ?? '')),
                                        TextEntry::make('discount_amount')
                                            ->label(__('Discount'))
                                            ->numeric(locale: 'en')
                                            ->suffix(fn ($record) => ' '.($record->currency?->symbol ?? ''))
                                            ->color('warning'),
                                        TextEntry::make('tax_amount')
                                            ->label(__('Tax'))
                                            ->numeric(locale: 'en')
                                            ->suffix(fn ($record) => ' '.($record->currency?->symbol ?? ''))
                                            ->placeholder('0.00'),
                                    ]),
                                Grid::make(1)
                                    ->schema([
                                        TextEntry::make('total_amount')
                                            ->label(__('Total Amount'))
                                            ->numeric(locale: 'en')
                                            ->suffix(fn ($record) => ' '.($record->currency?->symbol ?? ''))
                                            ->weight(FontWeight::Bold),
                                        TextEntry::make('paid_amount')
                                            ->label(__('Paid Amount'))
                                            ->numeric(locale: 'en')
                                            ->suffix(fn ($record) => ' '.($record->currency?->symbol ?? ''))
                                            ->color('success'),
                                        TextEntry::make('amount_due')
                                            ->label(__('Amount Due'))
                                            ->numeric(locale: 'en')
                                            ->suffix(fn ($record) => ' '.($record->currency?->symbol ?? ''))
                                            ->color(fn ($record) => $record->amount_due > 0 ? 'danger' : 'success')
                                            ->weight(FontWeight::Bold),
                                    ]),
                            ]),
                    ]),

                Section::make(__('Additional Information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('notes')
                                    ->label(__('Notes'))
                                    ->placeholder(__('No notes'))
                                    ->columnSpanFull(),
                                TextEntry::make('created_at')
                                    ->label(__('Created At'))
                                    ->dateTime(),
                                TextEntry::make('updated_at')
                                    ->label(__('Last Updated'))
                                    ->dateTime(),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getLabel(): ?string
    {
        return __('Purchase');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Purchases');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPurchases::route('/'),
            'create' => CreatePurchase::route('/create'),
            'view' => ViewPurchase::route('/{record}'),
            'edit' => EditPurchase::route('/{record}/edit'),
            'invoice' => PurchaseInvoice::route('/{record}/invoice'),
        ];
    }
}
