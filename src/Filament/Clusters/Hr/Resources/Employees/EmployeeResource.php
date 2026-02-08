<?php

namespace Gopos\Filament\Clusters\Hr\Resources\Employees;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Gopos\Filament\Clusters\Hr\HrCluster;
use Gopos\Filament\Clusters\Hr\Resources\Employees\Pages\CreateEmployee;
use Gopos\Filament\Clusters\Hr\Resources\Employees\Pages\EditEmployee;
use Gopos\Filament\Clusters\Hr\Resources\Employees\Pages\ListEmployees;
use Gopos\Filament\Clusters\Hr\Resources\Employees\Schemas\EmployeeForm;
use Gopos\Filament\Clusters\Hr\Resources\Employees\Tables\EmployeesTable;
use Gopos\Models\Employee;

class EmployeeResource extends Resource
{
    protected static ?string $cluster = HrCluster::class;

    protected static ?string $model = Employee::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('Employees');
    }

    public static function getNavigationLabel(): string
    {
        return __('Employees');
    }

    public static function getLabel(): string
    {
        return __('Employee');
    }

    public static function getPluralLabel(): string
    {
        return __('Employees');
    }

    public static function form(Schema $schema): Schema
    {
        return EmployeeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployees::route('/'),
            'create' => CreateEmployee::route('/create'),
            'edit' => EditEmployee::route('/{record}/edit'),
        ];
    }
}
