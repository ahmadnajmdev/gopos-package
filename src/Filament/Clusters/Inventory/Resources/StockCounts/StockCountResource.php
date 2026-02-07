<?php

namespace Gopos\Filament\Clusters\Inventory\Resources\StockCounts;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Gopos\Filament\Clusters\Inventory\InventoryCluster;
use Gopos\Filament\Clusters\Inventory\Resources\StockCounts\Pages\CreateStockCount;
use Gopos\Filament\Clusters\Inventory\Resources\StockCounts\Pages\EditStockCount;
use Gopos\Filament\Clusters\Inventory\Resources\StockCounts\Pages\ListStockCounts;
use Gopos\Filament\Clusters\Inventory\Resources\StockCounts\Pages\ViewStockCount;
use Gopos\Models\StockCount;
use Gopos\Models\Warehouse;

class StockCountResource extends Resource
{
    protected static ?string $cluster = InventoryCluster::class;

    protected static ?string $model = StockCount::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('Inventory');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Stock Count Information'))
                    ->columns(2)
                    ->schema([
                        Select::make('warehouse_id')
                            ->label(__('Warehouse'))
                            ->options(Warehouse::active()->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(fn ($record) => $record && $record->status !== 'draft'),
                        Select::make('type')
                            ->label(__('Count Type'))
                            ->options([
                                'full' => __('Full Count'),
                                'partial' => __('Partial Count'),
                                'cycle' => __('Cycle Count'),
                            ])
                            ->required()
                            ->default('full')
                            ->disabled(fn ($record) => $record && $record->status !== 'draft'),
                        DatePicker::make('count_date')
                            ->label(__('Count Date'))
                            ->required()
                            ->default(now()),
                        Select::make('status')
                            ->label(__('Status'))
                            ->options([
                                'draft' => __('Draft'),
                                'in_progress' => __('In Progress'),
                                'completed' => __('Completed'),
                                'cancelled' => __('Cancelled'),
                            ])
                            ->default('draft')
                            ->disabled(),
                        Textarea::make('notes')
                            ->label(__('Notes'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('count_number')
                    ->label(__('Count #'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('warehouse.name')
                    ->label(__('Warehouse'))
                    ->searchable(),
                TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'full' => __('Full'),
                        'partial' => __('Partial'),
                        'cycle' => __('Cycle'),
                        default => $state,
                    }),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => __('Draft'),
                        'in_progress' => __('In Progress'),
                        'completed' => __('Completed'),
                        'cancelled' => __('Cancelled'),
                        default => $state,
                    }),
                TextColumn::make('count_date')
                    ->label(__('Date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('items_count')
                    ->label(__('Items'))
                    ->counts('items'),
                IconColumn::make('adjustments_posted')
                    ->label(__('Posted'))
                    ->boolean(),
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
                        'in_progress' => __('In Progress'),
                        'completed' => __('Completed'),
                        'cancelled' => __('Cancelled'),
                    ]),
                SelectFilter::make('warehouse_id')
                    ->label(__('Warehouse'))
                    ->options(Warehouse::pluck('name', 'id')),
                SelectFilter::make('type')
                    ->label(__('Type'))
                    ->options([
                        'full' => __('Full Count'),
                        'partial' => __('Partial Count'),
                        'cycle' => __('Cycle Count'),
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn ($record) => in_array($record->status, ['draft', 'in_progress'])),
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
                Section::make(__('Stock Count Information'))
                    ->schema([
                        TextEntry::make('count_number')
                            ->label(__('Count Number')),
                        TextEntry::make('status')
                            ->label(__('Status'))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'draft' => 'gray',
                                'in_progress' => 'warning',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('warehouse.name')
                            ->label(__('Warehouse')),
                        TextEntry::make('type')
                            ->label(__('Type'))
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'full' => __('Full Count'),
                                'partial' => __('Partial Count'),
                                'cycle' => __('Cycle Count'),
                                default => $state,
                            }),
                        TextEntry::make('count_date')
                            ->label(__('Count Date'))
                            ->date(),
                        TextEntry::make('started_at')
                            ->label(__('Started At'))
                            ->dateTime(),
                        TextEntry::make('completed_at')
                            ->label(__('Completed At'))
                            ->dateTime(),
                        IconEntry::make('adjustments_posted')
                            ->label(__('Adjustments Posted'))
                            ->boolean(),
                        TextEntry::make('creator.name')
                            ->label(__('Created By')),
                        TextEntry::make('completer.name')
                            ->label(__('Completed By')),
                        TextEntry::make('notes')
                            ->label(__('Notes'))
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStockCounts::route('/'),
            'create' => CreateStockCount::route('/create'),
            'view' => ViewStockCount::route('/{record}'),
            'edit' => EditStockCount::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): string
    {
        return __('Stock Count');
    }

    public static function getPluralLabel(): string
    {
        return __('Stock Counts');
    }
}
