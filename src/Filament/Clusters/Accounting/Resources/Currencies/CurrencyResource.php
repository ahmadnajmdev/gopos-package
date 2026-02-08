<?php

namespace Gopos\Filament\Clusters\Accounting\Resources\Currencies;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Gopos\Filament\Clusters\Accounting\AccountingCluster;
use Gopos\Filament\Clusters\Accounting\Resources\Currencies\Pages\CreateCurrency;
use Gopos\Filament\Clusters\Accounting\Resources\Currencies\Pages\EditCurrency;
use Gopos\Filament\Clusters\Accounting\Resources\Currencies\Pages\ListCurrencies;
use Gopos\Filament\Clusters\Accounting\Resources\Currencies\Pages\ViewCurrency;
use Gopos\Filament\Clusters\Accounting\Resources\Currencies\Schemas\CurrencyForm;
use Gopos\Filament\Clusters\Accounting\Resources\Currencies\Schemas\CurrencyInfolist;
use Gopos\Filament\Clusters\Accounting\Resources\Currencies\Tables\CurrenciesTable;
use Gopos\Models\Currency;

class CurrencyResource extends Resource
{
    protected static ?string $cluster = AccountingCluster::class;

    protected static ?string $model = Currency::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static ?int $navigationSort = 15;

    protected static bool $isScopedToTenant = false;

    public static function getNavigationGroup(): ?string
    {
        return __('Settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('Currencies');
    }

    public static function getLabel(): string
    {
        return __('Currency');
    }

    public static function getPluralLabel(): string
    {
        return __('Currencies');
    }

    public static function form(Schema $schema): Schema
    {
        return CurrencyForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CurrencyInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CurrenciesTable::configure($table);
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
            'index' => ListCurrencies::route('/'),
            'create' => CreateCurrency::route('/create'),
            'view' => ViewCurrency::route('/{record}'),
            'edit' => EditCurrency::route('/{record}/edit'),
        ];
    }
}
