<?php

namespace Gopos\Filament\Clusters\Inventory\Resources\Products;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\ImageColumn;
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
use Gopos\Filament\Clusters\Inventory\InventoryCluster;
use Gopos\Filament\Clusters\Inventory\Resources\Categories\CategoryResource;
use Gopos\Filament\Clusters\Inventory\Resources\Products\Pages\CreateProduct;
use Gopos\Filament\Clusters\Inventory\Resources\Products\Pages\EditProduct;
use Gopos\Filament\Clusters\Inventory\Resources\Products\Pages\ListProducts;
use Gopos\Filament\Clusters\Inventory\Resources\Products\Pages\ViewProduct;
use Gopos\Filament\Clusters\Inventory\Resources\Units\UnitResource;
use Gopos\Models\Currency;
use Gopos\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $cluster = InventoryCluster::class;

    protected static ?string $model = Product::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    protected static ?int $navigationSort = 5;

    /**
     * Get the navigation group for this resource.
     *
     * @return string|null The name of the navigation group, or null if no group is specified.
     */
    public static function getNavigationGroup(): ?string
    {
        return __('Inventory');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Product Information'))
                    ->schema([
                        FileUpload::make('image')
                            ->maxSize(4096)
                            ->image(),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('barcode')
                            ->label(__('Barcode'))
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Select::make('category_id')
                            ->relationship('category', 'name')
                            ->createOptionForm(fn (Schema $schema) => CategoryResource::form($schema))
                            ->required(),

                        Textarea::make('description')
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(2)
                    ->columns(3),

                Section::make(__('Stock & Pricing'))
                    ->schema([
                        Select::make('unit_id')
                            ->relationship('unit', 'name')
                            ->createOptionForm(fn (Schema $schema) => UnitResource::form($schema))
                            ->required(),
                        Hidden::make('stock')
                            ->default(0)
                            ->required(),
                        TextInput::make('low_stock_alert')
                            ->numeric()
                            ->label(__('Low stock alert'))
                            ->helperText(__('Show alert when stock is below this number')),
                        TextInput::make('cost')
                            ->required()
                            ->numeric()
                            ->prefix(Currency::getBaseCurrency()?->symbol ?? ''),
                        TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix(Currency::getBaseCurrency()?->symbol ?? ''),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')->square(),
                TextColumn::make('category.name')
                    ->numeric(locale: 'en')
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('barcode')
                    ->label(__('Barcode'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('price')
                    ->numeric(locale: 'en')
                    ->suffix(' '.(Currency::getBaseCurrency()?->symbol ?? ''))
                    ->sortable(),
                TextColumn::make('stock')
                    ->numeric(locale: 'en')
                    ->suffix(function ($record) {
                        return $record->stock > 0 ? ' '.$record->unit?->abbreviation : '';
                    })
                    ->sortable(),
                TextColumn::make('low_stock_alert')
                    ->label(__('Low alert'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label(__('Category'))
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('unit_id')
                    ->label(__('Unit'))
                    ->relationship('unit', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('low_stock')
                    ->label(__('Low Stock'))
                    ->query(fn (Builder $query): Builder => $query->whereColumn('stock', '<=', 'low_stock_alert'))
                    ->toggle(),
                QueryBuilder::make()
                    ->constraints([
                        TextConstraint::make('name')
                            ->label(__('Name')),
                        TextConstraint::make('barcode')
                            ->label(__('Barcode')),
                        RelationshipConstraint::make('category')
                            ->label(__('Category'))
                            ->relationship('category', 'name'),
                        NumberConstraint::make('price')
                            ->label(__('Price')),
                        NumberConstraint::make('cost')
                            ->label(__('Cost')),
                        NumberConstraint::make('stock')
                            ->label(__('Stock')),
                        NumberConstraint::make('low_stock_alert')
                            ->label(__('Low Stock Alert')),
                        DateConstraint::make('created_at')
                            ->label(__('Created at')),
                    ]),
            ])
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('mark_damaged')
                        ->label(__('Mark as Damaged'))
                        ->icon('heroicon-o-exclamation-triangle')
                        ->color('danger')
                        ->form([
                            TextInput::make('quantity')
                                ->label(__('Quantity'))
                                ->numeric()
                                ->required()
                                ->minValue(1),
                            Textarea::make('reason')
                                ->label(__('Reason'))
                                ->rows(2),
                        ])
                        ->action(function (array $data, $records) {
                            foreach ($records as $product) {
                                \Gopos\Models\InventoryMovement::create([
                                    'product_id' => $product->id,
                                    'type' => 'damaged',
                                    'quantity' => -(int) $data['quantity'],
                                    'user_id' => auth()->id(),
                                    'reason' => $data['reason'] ?? null,
                                    'movement_date' => now(),
                                ]);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function infolist(Schema $schema): Schema
    {
        $currencySymbol = Currency::getBaseCurrency()?->symbol ?? '$';

        return $schema
            ->components([
                Section::make(__('Product Information'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                ImageEntry::make('image')
                                    ->label(__('Image'))
                                    ->square()
                                    ->size(120),
                                Grid::make(1)
                                    ->schema([
                                        TextEntry::make('name')
                                            ->label(__('Product Name'))
                                            ->weight(FontWeight::Bold)
                                            ->copyable()
                                            ->copyMessage(__('Copied!')),
                                        TextEntry::make('barcode')
                                            ->label(__('Barcode'))
                                            ->placeholder(__('No barcode'))
                                            ->copyable()
                                            ->copyMessage(__('Copied!')),
                                        TextEntry::make('category.name')
                                            ->label(__('Category'))
                                            ->badge()
                                            ->color('info'),
                                    ])
                                    ->columnSpan(2),
                            ]),
                        TextEntry::make('description')
                            ->label(__('Description'))
                            ->placeholder(__('No description'))
                            ->columnSpanFull(),
                    ]),

                Section::make(__('Stock & Pricing'))
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('stock')
                                    ->label(__('Current Stock'))
                                    ->numeric(locale: 'en')
                                    ->weight(FontWeight::Bold)
                                    ->color(fn ($record) => $record->stock <= ($record->low_stock_alert ?? 0) ? 'danger' : 'success')
                                    ->suffix(fn ($record) => ' '.($record->unit?->abbreviation ?? '')),
                                TextEntry::make('low_stock_alert')
                                    ->label(__('Low Stock Alert'))
                                    ->numeric(locale: 'en')
                                    ->placeholder(__('Not set')),
                                TextEntry::make('cost')
                                    ->label(__('Cost'))
                                    ->numeric(locale: 'en')
                                    ->suffix(fn () => ' '.$currencySymbol),
                                TextEntry::make('price')
                                    ->label(__('Price'))
                                    ->numeric(locale: 'en')
                                    ->suffix(fn () => ' '.$currencySymbol)
                                    ->weight(FontWeight::Bold),
                            ]),
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('unit.name')
                                    ->label(__('Unit'))
                                    ->badge()
                                    ->color('gray'),
                                TextEntry::make('average_cost')
                                    ->label(__('Avg. Cost'))
                                    ->numeric(locale: 'en', decimalPlaces: 4)
                                    ->suffix(fn () => ' '.$currencySymbol)
                                    ->placeholder('0.0000'),
                                TextEntry::make('track_batches')
                                    ->label(__('Batch Tracking'))
                                    ->badge()
                                    ->getStateUsing(fn ($record) => $record->track_batches ? __('Enabled') : __('Disabled'))
                                    ->color(fn ($record) => $record->track_batches ? 'success' : 'gray'),
                                TextEntry::make('track_serials')
                                    ->label(__('Serial Tracking'))
                                    ->badge()
                                    ->getStateUsing(fn ($record) => $record->track_serials ? __('Enabled') : __('Disabled'))
                                    ->color(fn ($record) => $record->track_serials ? 'success' : 'gray'),
                            ]),
                    ]),

                Section::make(__('Additional Information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('has_expiry')
                                    ->label(__('Has Expiry'))
                                    ->badge()
                                    ->getStateUsing(fn ($record) => $record->has_expiry ? __('Yes') : __('No'))
                                    ->color(fn ($record) => $record->has_expiry ? 'warning' : 'gray'),
                                TextEntry::make('expiry_warning_days')
                                    ->label(__('Expiry Warning Days'))
                                    ->numeric(locale: 'en')
                                    ->placeholder('-'),
                                TextEntry::make('warranty_months')
                                    ->label(__('Warranty (Months)'))
                                    ->numeric(locale: 'en')
                                    ->placeholder('-'),
                                TextEntry::make('created_at')
                                    ->label(__('Created At'))
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
            \Gopos\Filament\Clusters\Inventory\Resources\Products\RelationManagers\MovementsRelationManager::class,
        ];
    }

    public static function getLabel(): string
    {
        return __('Product');
    }

    public static function getPluralLabel(): string
    {
        return __('Products');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'view' => ViewProduct::route('/{record}'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
