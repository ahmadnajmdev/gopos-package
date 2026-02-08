<?php

namespace Gopos\Filament\Clusters\Hr\Resources\Payrolls;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Gopos\Filament\Clusters\Hr\HrCluster;
use Gopos\Filament\Clusters\Hr\Resources\Payrolls\Pages\CreatePayroll;
use Gopos\Filament\Clusters\Hr\Resources\Payrolls\Pages\EditPayroll;
use Gopos\Filament\Clusters\Hr\Resources\Payrolls\Pages\ListPayrolls;
use Gopos\Filament\Clusters\Hr\Resources\Payrolls\Schemas\PayrollForm;
use Gopos\Filament\Clusters\Hr\Resources\Payrolls\Tables\PayrollsTable;
use Gopos\Models\Payroll;

class PayrollResource extends Resource
{
    protected static ?string $cluster = HrCluster::class;

    protected static ?string $model = Payroll::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?int $navigationSort = 8;

    public static function getNavigationGroup(): ?string
    {
        return __('Payroll');
    }

    public static function getNavigationLabel(): string
    {
        return __('Payrolls');
    }

    public static function getLabel(): string
    {
        return __('Payroll');
    }

    public static function getPluralLabel(): string
    {
        return __('Payrolls');
    }

    public static function form(Schema $schema): Schema
    {
        return PayrollForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PayrollsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayrolls::route('/'),
            'create' => CreatePayroll::route('/create'),
            'edit' => EditPayroll::route('/{record}/edit'),
        ];
    }
}
