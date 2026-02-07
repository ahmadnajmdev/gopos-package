<?php

namespace Gopos\Filament\Clusters\Sales\Resources\SaleReturns;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Gopos\Filament\Clusters\Sales\Resources\SaleReturns\Pages\CreateSaleReturn;
use Gopos\Filament\Clusters\Sales\Resources\SaleReturns\Pages\EditSaleReturn;
use Gopos\Filament\Clusters\Sales\Resources\SaleReturns\Pages\ListSaleReturns;
use Gopos\Filament\Clusters\Sales\Resources\SaleReturns\Pages\ViewSaleReturn;
use Gopos\Filament\Clusters\Sales\Resources\SaleReturns\Schemas\SaleReturnForm;
use Gopos\Filament\Clusters\Sales\Resources\SaleReturns\Schemas\SaleReturnInfolist;
use Gopos\Filament\Clusters\Sales\Resources\SaleReturns\Tables\SaleReturnsTable;
use Gopos\Filament\Clusters\Sales\SalesCluster;
use Gopos\Models\SaleReturn;

class SaleReturnResource extends Resource
{
    protected static ?string $cluster = SalesCluster::class;

    protected static ?string $model = SaleReturn::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowUturnLeft;

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return SaleReturnForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SaleReturnInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SaleReturnsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getLabel(): string
    {
        return __('Sale Return');
    }

    public static function getPluralLabel(): string
    {
        return __('Sale Returns');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSaleReturns::route('/'),
            'create' => CreateSaleReturn::route('/create'),
            'view' => ViewSaleReturn::route('/{record}'),
            'edit' => EditSaleReturn::route('/{record}/edit'),
        ];
    }
}
