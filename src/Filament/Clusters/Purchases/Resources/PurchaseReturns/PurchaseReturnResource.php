<?php

namespace Gopos\Filament\Clusters\Purchases\Resources\PurchaseReturns;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Gopos\Filament\Clusters\Purchases\PurchasesCluster;
use Gopos\Filament\Clusters\Purchases\Resources\PurchaseReturns\Pages\CreatePurchaseReturn;
use Gopos\Filament\Clusters\Purchases\Resources\PurchaseReturns\Pages\EditPurchaseReturn;
use Gopos\Filament\Clusters\Purchases\Resources\PurchaseReturns\Pages\ListPurchaseReturns;
use Gopos\Filament\Clusters\Purchases\Resources\PurchaseReturns\Pages\ViewPurchaseReturn;
use Gopos\Filament\Clusters\Purchases\Resources\PurchaseReturns\Schemas\PurchaseReturnForm;
use Gopos\Filament\Clusters\Purchases\Resources\PurchaseReturns\Schemas\PurchaseReturnInfolist;
use Gopos\Filament\Clusters\Purchases\Resources\PurchaseReturns\Tables\PurchaseReturnsTable;
use Gopos\Models\PurchaseReturn;

class PurchaseReturnResource extends Resource
{
    protected static ?string $cluster = PurchasesCluster::class;

    protected static ?string $model = PurchaseReturn::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowUturnLeft;

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return PurchaseReturnForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PurchaseReturnInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PurchaseReturnsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getLabel(): string
    {
        return __('Purchase Return');
    }

    public static function getPluralLabel(): string
    {
        return __('Purchase Returns');
    }

    public static function getNavigationGroup(): string
    {
        return __('Purchases');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPurchaseReturns::route('/'),
            'create' => CreatePurchaseReturn::route('/create'),
            'view' => ViewPurchaseReturn::route('/{record}'),
            'edit' => EditPurchaseReturn::route('/{record}/edit'),
        ];
    }
}
