<?php

namespace Gopos\Filament\Clusters\Inventory\Resources\StockTransfers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Gopos\Filament\Clusters\Inventory\InventoryCluster;
use Gopos\Filament\Clusters\Inventory\Resources\StockTransfers\Pages\CreateStockTransfer;
use Gopos\Filament\Clusters\Inventory\Resources\StockTransfers\Pages\EditStockTransfer;
use Gopos\Filament\Clusters\Inventory\Resources\StockTransfers\Pages\ListStockTransfers;
use Gopos\Filament\Clusters\Inventory\Resources\StockTransfers\Pages\ViewStockTransfer;
use Gopos\Models\Product;
use Gopos\Models\StockTransfer;
use Gopos\Models\Warehouse;

class StockTransferResource extends Resource
{
    protected static ?string $cluster = InventoryCluster::class;

    protected static ?string $model = StockTransfer::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('Inventory');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Transfer Information'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('transfer_number')
                            ->label(__('Transfer Number'))
                            ->default(fn () => StockTransfer::generateTransferNumber())
                            ->disabled()
                            ->dehydrated(),
                        Select::make('from_warehouse_id')
                            ->label(__('From Warehouse'))
                            ->options(Warehouse::active()->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live(),
                        Select::make('to_warehouse_id')
                            ->label(__('To Warehouse'))
                            ->options(Warehouse::active()->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->different('from_warehouse_id'),
                        DatePicker::make('transfer_date')
                            ->label(__('Transfer Date'))
                            ->required()
                            ->default(now()),
                        DatePicker::make('expected_date')
                            ->label(__('Expected Arrival')),
                        Select::make('status')
                            ->label(__('Status'))
                            ->options([
                                'draft' => __('Draft'),
                                'pending' => __('Pending'),
                                'in_transit' => __('In Transit'),
                                'partial' => __('Partial'),
                                'completed' => __('Completed'),
                                'cancelled' => __('Cancelled'),
                            ])
                            ->default('draft')
                            ->disabled(fn ($record) => $record && ! in_array($record->status, ['draft', 'pending'])),
                        Textarea::make('notes')
                            ->label(__('Notes'))
                            ->columnSpanFull(),
                    ]),

                Section::make(__('Transfer Items'))
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->label('')
                            ->schema([
                                Select::make('product_id')
                                    ->label(__('Product'))
                                    ->options(Product::pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(3),
                                TextInput::make('quantity_requested')
                                    ->label(__('Quantity'))
                                    ->numeric()
                                    ->required()
                                    ->minValue(0.0001)
                                    ->columnSpan(1),
                                TextInput::make('unit_cost')
                                    ->label(__('Unit Cost'))
                                    ->numeric()
                                    ->prefix('$')
                                    ->columnSpan(1),
                                Textarea::make('notes')
                                    ->label(__('Notes'))
                                    ->rows(1)
                                    ->columnSpan(2),
                            ])
                            ->columns(7)
                            ->defaultItems(1)
                            ->addActionLabel(__('Add Item'))
                            ->reorderable(false)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transfer_number')
                    ->label(__('Transfer #'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('fromWarehouse.name')
                    ->label(__('From'))
                    ->searchable(),
                TextColumn::make('toWarehouse.name')
                    ->label(__('To'))
                    ->searchable(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'pending' => 'warning',
                        'in_transit' => 'info',
                        'partial' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => __('Draft'),
                        'pending' => __('Pending'),
                        'in_transit' => __('In Transit'),
                        'partial' => __('Partial'),
                        'completed' => __('Completed'),
                        'cancelled' => __('Cancelled'),
                        default => $state,
                    }),
                TextColumn::make('transfer_date')
                    ->label(__('Date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('items_count')
                    ->label(__('Items'))
                    ->counts('items'),
                TextColumn::make('creator.name')
                    ->label(__('Created By'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'draft' => __('Draft'),
                        'pending' => __('Pending'),
                        'in_transit' => __('In Transit'),
                        'partial' => __('Partial'),
                        'completed' => __('Completed'),
                        'cancelled' => __('Cancelled'),
                    ]),
                SelectFilter::make('from_warehouse_id')
                    ->label(__('From Warehouse'))
                    ->options(Warehouse::pluck('name', 'id')),
                SelectFilter::make('to_warehouse_id')
                    ->label(__('To Warehouse'))
                    ->options(Warehouse::pluck('name', 'id')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn ($record) => $record->canEdit()),
                DeleteAction::make()
                    ->visible(fn ($record) => $record->status === 'draft'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Transfer Information'))
                    ->schema([
                        TextEntry::make('transfer_number')
                            ->label(__('Transfer Number')),
                        TextEntry::make('status')
                            ->label(__('Status'))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'draft' => 'gray',
                                'pending' => 'warning',
                                'in_transit' => 'info',
                                'partial' => 'warning',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('fromWarehouse.name')
                            ->label(__('From Warehouse')),
                        TextEntry::make('toWarehouse.name')
                            ->label(__('To Warehouse')),
                        TextEntry::make('transfer_date')
                            ->label(__('Transfer Date'))
                            ->date(),
                        TextEntry::make('expected_date')
                            ->label(__('Expected Arrival'))
                            ->date(),
                        TextEntry::make('received_date')
                            ->label(__('Received Date'))
                            ->date(),
                        TextEntry::make('creator.name')
                            ->label(__('Created By')),
                        TextEntry::make('approver.name')
                            ->label(__('Approved By')),
                        TextEntry::make('receiver.name')
                            ->label(__('Received By')),
                        TextEntry::make('notes')
                            ->label(__('Notes'))
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make(__('Transfer Items'))
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                TextEntry::make('product.name')
                                    ->label(__('Product')),
                                TextEntry::make('quantity_requested')
                                    ->label(__('Requested')),
                                TextEntry::make('quantity_sent')
                                    ->label(__('Sent')),
                                TextEntry::make('quantity_received')
                                    ->label(__('Received')),
                                TextEntry::make('unit_cost')
                                    ->label(__('Unit Cost'))
                                    ->money('USD'),
                            ])
                            ->columns(5),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStockTransfers::route('/'),
            'create' => CreateStockTransfer::route('/create'),
            'view' => ViewStockTransfer::route('/{record}'),
            'edit' => EditStockTransfer::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): string
    {
        return __('Stock Transfer');
    }

    public static function getPluralLabel(): string
    {
        return __('Stock Transfers');
    }
}
