<?php

namespace Gopos\Filament\Clusters\Hr\Resources\Leaves;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Gopos\Filament\Clusters\Hr\HrCluster;
use Gopos\Filament\Clusters\Hr\Resources\Leaves\Pages\CreateLeave;
use Gopos\Filament\Clusters\Hr\Resources\Leaves\Pages\EditLeave;
use Gopos\Filament\Clusters\Hr\Resources\Leaves\Pages\ListLeaves;
use Gopos\Filament\Clusters\Hr\Resources\Leaves\Schemas\LeaveForm;
use Gopos\Filament\Clusters\Hr\Resources\Leaves\Tables\LeavesTable;
use Gopos\Models\Leave;

class LeaveResource extends Resource
{
    protected static ?string $cluster = HrCluster::class;

    protected static ?string $model = Leave::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?int $navigationSort = 6;

    public static function getNavigationGroup(): ?string
    {
        return __('Leave Management');
    }

    public static function getNavigationLabel(): string
    {
        return __('Leave Requests');
    }

    public static function getLabel(): string
    {
        return __('Leave Request');
    }

    public static function getPluralLabel(): string
    {
        return __('Leave Requests');
    }

    public static function form(Schema $schema): Schema
    {
        return LeaveForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeavesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLeaves::route('/'),
            'create' => CreateLeave::route('/create'),
            'edit' => EditLeave::route('/{record}/edit'),
        ];
    }
}
