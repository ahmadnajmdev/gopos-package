<?php

namespace Gopos\Filament\Clusters\Sales\Resources\Quotations;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Gopos\Enums\QuotationStatus;
use Gopos\Filament\Clusters\Sales\Resources\Customers\CustomerResource;
use Gopos\Filament\Clusters\Sales\Resources\Quotations\Pages\CreateQuotation;
use Gopos\Filament\Clusters\Sales\Resources\Quotations\Pages\EditQuotation;
use Gopos\Filament\Clusters\Sales\Resources\Quotations\Pages\ListQuotations;
use Gopos\Filament\Clusters\Sales\Resources\Quotations\Pages\ViewQuotation;
use Gopos\Filament\Clusters\Sales\Resources\Sales\SaleResource;
use Gopos\Filament\Clusters\Sales\SalesCluster;
use Gopos\Models\Currency;
use Gopos\Models\Product;
use Gopos\Models\Quotation;
use Gopos\Models\Sale;
use Gopos\Models\SaleItem;
use Illuminate\Database\Eloquent\Builder;

class QuotationResource extends Resource
{
    protected static ?string $cluster = SalesCluster::class;

    protected static ?string $model = Quotation::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?int $navigationSort = 5;

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
                Section::make(__('Quotation Details'))
                    ->columns(3)
                    ->schema([
                        TextInput::make('quotation_number')
                            ->required()
                            ->default(fn () => Quotation::generateQuotationNumber())
                            ->readOnly()
                            ->maxLength(255),
                        Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->placeholder(__('Walk-in Customer'))
                            ->createOptionForm(fn (Schema $schema) => CustomerResource::form($schema)),
                        DatePicker::make('quotation_date')
                            ->default(now())
                            ->required(),
                        DatePicker::make('valid_until')
                            ->label(__('Valid Until'))
                            ->default(now()->addDays(30)),
                        Select::make('status')
                            ->options(QuotationStatus::class)
                            ->default(QuotationStatus::Draft)
                            ->required(),
                        Hidden::make('currency_id')
                            ->default(fn () => Currency::getBaseCurrency()?->id),
                    ])
                    ->columnSpanFull(),
                Section::make(__('Products'))
                    ->schema([
                        Repeater::make('products')
                            ->relationship('items')
                            ->live()
                            ->defaultItems(0)
                            ->table([
                                TableColumn::make(__('Product')),
                                TableColumn::make(__('Quantity')),
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
                                    ->label(__('Quantity'))
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
                Section::make(__('Quotation Total'))
                    ->schema([
                        TextInput::make('sub_total')
                            ->label(__('Subtotal'))
                            ->required()
                            ->readOnly()
                            ->suffix(' '.Currency::getBaseCurrency()?->symbol),
                        TextInput::make('discount')
                            ->label(__('Discount'))
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                $set('total_amount', (float) $get('sub_total') - (float) $get('discount'));
                            })
                            ->maxValue(fn (Get $get) => $get('sub_total'))
                            ->suffix(' '.Currency::getBaseCurrency()?->symbol)
                            ->default(0.00),
                        TextInput::make('total_amount')
                            ->label(__('Total'))
                            ->required()
                            ->readOnly()
                            ->suffix(' '.Currency::getBaseCurrency()?->symbol),
                    ])
                    ->columns(3)
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
                TextColumn::make('quotation_number')
                    ->label(__('Quotation #'))
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold),
                TextColumn::make('customer.name')
                    ->label(__('Customer'))
                    ->searchable()
                    ->placeholder(__('Walk-in Customer'))
                    ->sortable(),
                TextColumn::make('quotation_date')
                    ->label(__('Date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('valid_until')
                    ->label(__('Valid Until'))
                    ->date()
                    ->sortable()
                    ->color(fn ($record) => $record->isExpired() ? 'danger' : null),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label(__('Total'))
                    ->numeric(locale: 'en')
                    ->suffix(' '.Currency::getBaseCurrency()?->symbol)
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options(QuotationStatus::class),
                SelectFilter::make('customer_id')
                    ->label(__('Customer'))
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('expired')
                    ->label(__('Expired'))
                    ->query(fn (Builder $query): Builder => $query->where('valid_until', '<', now()))
                    ->toggle(),
                Filter::make('today')
                    ->label(__('Today'))
                    ->query(fn (Builder $query): Builder => $query->whereDate('quotation_date', today()))
                    ->toggle(),
            ])
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                Action::make('convert_to_sale')
                    ->label(__('Convert to Sale'))
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->isConvertible())
                    ->requiresConfirmation()
                    ->modalHeading(__('Convert Quotation to Sale'))
                    ->modalDescription(__('This will create a new sale from this quotation and mark it as accepted.'))
                    ->action(function ($record) {
                        $sale = self::convertToSale($record);

                        Notification::make()
                            ->title(__('Quotation converted to sale successfully'))
                            ->success()
                            ->send();

                        return redirect(SaleResource::getUrl('invoice', ['record' => $sale]));
                    }),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('quotation_date', 'desc');
    }

    public static function convertToSale(Quotation $quotation): Sale
    {
        $sale = Sale::create([
            'branch_id' => $quotation->branch_id,
            'customer_id' => $quotation->customer_id,
            'currency_id' => $quotation->currency_id,
            'exchange_rate' => $quotation->exchange_rate,
            'sale_date' => now(),
            'tax_code_id' => $quotation->tax_code_id,
            'tax_rate' => $quotation->tax_rate ?? 0,
            'tax_amount' => $quotation->tax_amount ?? 0,
            'tax_amount_in_base_currency' => $quotation->tax_amount_in_base_currency ?? 0,
            'sub_total' => $quotation->sub_total,
            'discount' => $quotation->discount,
            'total_amount' => $quotation->total_amount,
            'paid_amount' => 0,
            'note' => __('Converted from quotation :number', ['number' => $quotation->quotation_number]),
        ]);

        foreach ($quotation->items as $item) {
            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $item->product_id,
                'price' => $item->price,
                'stock' => $item->stock,
                'total_amount' => $item->total_amount,
            ]);
        }

        $quotation->update([
            'status' => QuotationStatus::Accepted,
            'converted_sale_id' => $sale->id,
        ]);

        return $sale;
    }

    public static function getLabel(): string
    {
        return __('Quotation');
    }

    public static function getPluralLabel(): string
    {
        return __('Quotations');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQuotations::route('/'),
            'create' => CreateQuotation::route('/create'),
            'edit' => EditQuotation::route('/{record}/edit'),
            'view' => ViewQuotation::route('/{record}'),
        ];
    }
}
