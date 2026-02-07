<?php

namespace Gopos\Filament\Clusters\Inventory\Resources\Warehouses;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Gopos\Filament\Clusters\Inventory\InventoryCluster;
use Gopos\Filament\Clusters\Inventory\Resources\Warehouses\Pages\CreateWarehouse;
use Gopos\Filament\Clusters\Inventory\Resources\Warehouses\Pages\EditWarehouse;
use Gopos\Filament\Clusters\Inventory\Resources\Warehouses\Pages\ListWarehouses;
use Gopos\Filament\Clusters\Inventory\Resources\Warehouses\Pages\ViewWarehouse;
use Gopos\Models\User;
use Gopos\Models\Warehouse;

class WarehouseResource extends Resource
{
    protected static ?string $cluster = InventoryCluster::class;

    protected static ?string $model = Warehouse::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('Inventory');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Warehouse Information'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Name (English)'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('name_ar')
                            ->label(__('Name (Arabic)'))
                            ->maxLength(255),
                        TextInput::make('name_ckb')
                            ->label(__('Name (Kurdish)'))
                            ->maxLength(255),
                        TextInput::make('code')
                            ->label(__('Code'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),
                        Textarea::make('address')
                            ->label(__('Address'))
                            ->columnSpanFull(),
                        TextInput::make('phone')
                            ->label(__('Phone'))
                            ->tel()
                            ->maxLength(50),
                        TextInput::make('email')
                            ->label(__('Email'))
                            ->email()
                            ->maxLength(255),
                        Select::make('manager_id')
                            ->label(__('Manager'))
                            ->options(User::pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                        Toggle::make('is_active')
                            ->label(__('Active'))
                            ->default(true),
                        Toggle::make('is_default')
                            ->label(__('Default Warehouse'))
                            ->helperText(__('This warehouse will be used by default for new operations')),
                        Toggle::make('allow_negative_stock')
                            ->label(__('Allow Negative Stock'))
                            ->helperText(__('Allow selling items even when stock is zero')),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label(__('Code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('manager.name')
                    ->label(__('Manager'))
                    ->searchable(),
                TextColumn::make('phone')
                    ->label(__('Phone'))
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),
                IconColumn::make('is_default')
                    ->label(__('Default'))
                    ->boolean(),
                TextColumn::make('products_count')
                    ->label(__('Products'))
                    ->counts('products'),
                TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('Status'))
                    ->placeholder(__('All warehouses'))
                    ->trueLabel(__('Active only'))
                    ->falseLabel(__('Inactive only')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Warehouse Information'))
                    ->schema([
                        TextEntry::make('code')
                            ->label(__('Code')),
                        TextEntry::make('name')
                            ->label(__('Name')),
                        TextEntry::make('name_ar')
                            ->label(__('Name (Arabic)')),
                        TextEntry::make('name_ckb')
                            ->label(__('Name (Kurdish)')),
                        TextEntry::make('address')
                            ->label(__('Address')),
                        TextEntry::make('phone')
                            ->label(__('Phone')),
                        TextEntry::make('email')
                            ->label(__('Email')),
                        TextEntry::make('manager.name')
                            ->label(__('Manager')),
                        IconEntry::make('is_active')
                            ->label(__('Active'))
                            ->boolean(),
                        IconEntry::make('is_default')
                            ->label(__('Default'))
                            ->boolean(),
                        IconEntry::make('allow_negative_stock')
                            ->label(__('Allow Negative Stock'))
                            ->boolean(),
                        TextEntry::make('created_at')
                            ->label(__('Created'))
                            ->dateTime('Y-m-d H:i:s'),
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
            'index' => ListWarehouses::route('/'),
            'create' => CreateWarehouse::route('/create'),
            'view' => ViewWarehouse::route('/{record}'),
            'edit' => EditWarehouse::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): string
    {
        return __('Warehouse');
    }

    public static function getPluralLabel(): string
    {
        return __('Warehouses');
    }
}
