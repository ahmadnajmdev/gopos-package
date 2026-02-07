<?php

namespace Gopos\Filament\Clusters\Sales\Resources\Sales;

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
use Gopos\Filament\Clusters\Sales\Resources\Customers\CustomerResource;
use Gopos\Filament\Clusters\Sales\Resources\SaleReturns\SaleReturnResource;
use Gopos\Filament\Clusters\Sales\Resources\Sales\Pages\CreateSale;
use Gopos\Filament\Clusters\Sales\Resources\Sales\Pages\EditSale;
use Gopos\Filament\Clusters\Sales\Resources\Sales\Pages\ListSales;
use Gopos\Filament\Clusters\Sales\Resources\Sales\Pages\SaleInvoice;
use Gopos\Filament\Clusters\Sales\Resources\Sales\Pages\ViewSale;
use Gopos\Filament\Clusters\Sales\SalesCluster;
use Gopos\Models\Currency;
use Gopos\Models\Product;
use Gopos\Models\Sale;
use Illuminate\Database\Eloquent\Builder;

class SaleResource extends Resource
{
    protected static ?string $cluster = SalesCluster::class;

    protected static ?string $model = Sale::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?int $navigationSort = 2;

    private static function calculateProductTotal(Set $set, Get $get): void
    {
        $product = Product::query()->find($get('product_id'));
        $price = $product->price ?? 0.00;
        $stock = $get('stock') ?? 0.00;
        $set('price', $price);
        $set('total_amount', (float) $stock * (float) $price);
        $products = $get('../../products') ?? [];
        $sub_total = 0.00;

        foreach ($products as $index => $product) {
            $sub_total += $product['total_amount'];
        }
        $set('../../sub_total', $sub_total);
        $set('../../total_amount', $sub_total);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Sale Details'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('sale_number')
                            ->required()
                            ->default(fn () => Sale::generateSaleNumber())
                            ->readOnly()
                            ->maxLength(255),
                        Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->placeholder(__('Walk-in Customer'))
                            ->createOptionForm(fn (Schema $schema) => CustomerResource::form($schema)),
                        DatePicker::make('sale_date')
                            ->default(now())
                            ->required(),

                    ])->columns(3)
                    ->columnSpanFull(),
                Section::make(__('Products'))
                    ->schema([
                        Repeater::make('products')
                            ->relationship('items')
                            ->live()
                            ->defaultItems(0)
                            ->table([
                                TableColumn::make(__('Product')),
                                TableColumn::make(__('Stock')),
                                TableColumn::make(__('Price')),
                                TableColumn::make(__('Total amount')),
                            ])
                            ->compact()
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateProductTotal($set, $get))
                            ->addActionLabel(__('Add Product'))
                            ->schema([
                                Select::make('product_id')
                                    ->relationship('product', 'name')
                                    ->live()
                                    ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateProductTotal($set, $get))
                                    ->required(),
                                TextInput::make('stock')
                                    ->suffix(function (Get $get) {
                                        return Product::query()->find($get('product_id'))->unit?->abbreviation ?? '';
                                    })
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateProductTotal($set, $get))
                                    ->required(),
                                TextInput::make('price')
                                    ->required()
                                    ->readOnly()
                                    ->suffix(' '.Currency::getBaseCurrency()?->symbol)
                                    ->default(0.00),
                                TextInput::make('total_amount')
                                    ->required()
                                    ->suffix(' '.Currency::getBaseCurrency()?->symbol)
                                    ->readOnly(),
                            ])
                            ->columns(4),
                    ])
                    ->columnSpanFull(),
                Section::make(__('Sale Payment'))
                    ->schema([
                        TextInput::make('sub_total')
                            ->label(__('Subtotal'))
                            ->required()
                            ->readOnly()
                            ->suffix(' '.Currency::getBaseCurrency()?->symbol),
                        TextInput::make('discount_amount')
                            ->label(__('Discount'))
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                $set('total_amount', (float) $get('sub_total') - (float) $get('discount_amount'));
                            })
                            ->maxValue(fn (Get $get) => $get('sub_total'))
                            ->suffix(' '.Currency::getBaseCurrency()?->symbol)
                            ->default(0.00),
                        TextInput::make('total_amount')
                            ->label(__('Total'))
                            ->required()
                            ->readOnly()
                            ->suffix(' '.Currency::getBaseCurrency()?->symbol),
                        TextInput::make('paid_amount')
                            ->label(__('Paid'))
                            ->required()
                            ->numeric()
                            ->maxValue(fn (Get $get) => $get('total_amount'))
                            ->suffix(' '.Currency::getBaseCurrency()?->symbol)
                            ->default(0.00),
                    ])
                    ->columns(4)
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sale_number')
                    ->label(__('Sale #'))
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold),
                TextColumn::make('customer.name')
                    ->label(__('Customer'))
                    ->searchable()
                    ->placeholder(__('Walk-in Customer'))
                    ->sortable(),
                TextColumn::make('sale_date')
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
                    ->suffix(' '.Currency::getBaseCurrency()?->symbol)
                    ->sortable(),
                TextColumn::make('paid_amount')
                    ->label(__('Paid'))
                    ->numeric(locale: 'en')
                    ->suffix(' '.Currency::getBaseCurrency()?->symbol)
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
                TextColumn::make('sub_total')
                    ->label(__('Subtotal'))
                    ->numeric(locale: 'en')
                    ->suffix(' '.Currency::getBaseCurrency()?->symbol)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('discount')
                    ->label(__('Discount'))
                    ->numeric(locale: 'en')
                    ->suffix(' '.Currency::getBaseCurrency()?->symbol)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('customer_id')
                    ->label(__('Customer'))
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('unpaid')
                    ->label(__('Unpaid/Partial'))
                    ->query(fn (Builder $query): Builder => $query->whereColumn('paid_amount', '<', 'total_amount'))
                    ->toggle(),
                Filter::make('today')
                    ->label(__('Today'))
                    ->query(fn (Builder $query): Builder => $query->whereDate('sale_date', today()))
                    ->toggle(),
                QueryBuilder::make()
                    ->constraints([
                        TextConstraint::make('sale_number')
                            ->label(__('Sale number')),
                        RelationshipConstraint::make('customer')
                            ->label(__('Customer'))
                            ->relationship('customer', 'name'),
                        NumberConstraint::make('sub_total')
                            ->label(__('Subtotal')),
                        NumberConstraint::make('discount')
                            ->label(__('Discount')),
                        NumberConstraint::make('total_amount')
                            ->label(__('Amount')),
                        NumberConstraint::make('paid_amount')
                            ->label(__('Paid amount')),
                        DateConstraint::make('sale_date')
                            ->label(__('Sale date')),
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
                Action::make('create_return')
                    ->label(__('Return'))
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->url(fn ($record) => SaleReturnResource::getUrl('create', parameters: [
                        'sale_id' => $record->id,
                    ])),
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sale_date', 'desc');
    }

    public static function infolist(Schema $schema): Schema
    {
        $currencySymbol = Currency::getBaseCurrency()?->symbol ?? '$';

        return $schema
            ->components([
                Section::make(__('Sale Information'))
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('sale_number')
                                    ->label(__('Sale Number'))
                                    ->weight(FontWeight::Bold)
                                    ->copyable()
                                    ->copyMessage(__('Copied!')),
                                TextEntry::make('sale_date')
                                    ->label(__('Sale Date'))
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

                Section::make(__('Customer Information'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('customer.name')
                                    ->label(__('Customer Name'))
                                    ->placeholder(__('Walk-in Customer'))
                                    ->icon('heroicon-o-user'),
                                TextEntry::make('customer.phone')
                                    ->label(__('Phone'))
                                    ->placeholder('-')
                                    ->icon('heroicon-o-phone'),
                                TextEntry::make('customer.email')
                                    ->label(__('Email'))
                                    ->placeholder('-')
                                    ->icon('heroicon-o-envelope'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make(__('Sale Items'))
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
                                TextEntry::make('price')
                                    ->label(__('Unit Price'))
                                    ->numeric(locale: 'en')
                                    ->suffix(fn () => ' '.$currencySymbol),
                                TextEntry::make('total_amount')
                                    ->label(__('Total'))
                                    ->numeric(locale: 'en')
                                    ->suffix(fn () => ' '.$currencySymbol)
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
                                            ->suffix(fn () => ' '.$currencySymbol),
                                        TextEntry::make('discount')
                                            ->label(__('Discount'))
                                            ->numeric(locale: 'en')
                                            ->suffix(fn () => ' '.$currencySymbol)
                                            ->color('warning'),
                                        TextEntry::make('tax_amount')
                                            ->label(__('Tax'))
                                            ->numeric(locale: 'en')
                                            ->suffix(fn () => ' '.$currencySymbol)
                                            ->placeholder('0.00'),
                                    ]),
                                Grid::make(1)
                                    ->schema([
                                        TextEntry::make('total_amount')
                                            ->label(__('Total Amount'))
                                            ->numeric(locale: 'en')
                                            ->suffix(fn () => ' '.$currencySymbol)
                                            ->weight(FontWeight::Bold),
                                        TextEntry::make('paid_amount')
                                            ->label(__('Paid Amount'))
                                            ->numeric(locale: 'en')
                                            ->suffix(fn () => ' '.$currencySymbol)
                                            ->color('success'),
                                        TextEntry::make('remaining_amount')
                                            ->label(__('Remaining Balance'))
                                            ->numeric(locale: 'en')
                                            ->suffix(fn () => ' '.$currencySymbol)
                                            ->color(fn ($record) => $record->remaining_amount > 0 ? 'danger' : 'success')
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

    public static function getLabel(): string
    {
        return __('Sale');
    }

    public static function getPluralLabel(): string
    {
        return __('Sales');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSales::route('/'),
            'create' => CreateSale::route('/create'),
            'edit' => EditSale::route('/{record}/edit'),
            'view' => ViewSale::route('/{record}'),
            'invoice' => SaleInvoice::route('/{record}/invoice'),
        ];
    }
}
