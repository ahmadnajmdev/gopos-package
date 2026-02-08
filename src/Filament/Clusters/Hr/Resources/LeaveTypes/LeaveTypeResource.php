<?php

namespace Gopos\Filament\Clusters\Hr\Resources\LeaveTypes;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Gopos\Filament\Clusters\Hr\HrCluster;
use Gopos\Filament\Clusters\Hr\Resources\LeaveTypes\Pages\ManageLeaveTypes;
use Gopos\Models\LeaveType;

class LeaveTypeResource extends Resource
{
    protected static ?string $cluster = HrCluster::class;

    protected static ?string $model = LeaveType::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?int $navigationSort = 5;

    protected static bool $isScopedToTenant = false;

    public static function getNavigationGroup(): ?string
    {
        return __('Leave Management');
    }

    public static function getLabel(): string
    {
        return __('Leave Type');
    }

    public static function getPluralLabel(): string
    {
        return __('Leave Types');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('name_ar')
                    ->label(__('Name (Arabic)'))
                    ->maxLength(255),
                TextInput::make('name_ckb')
                    ->label(__('Name (Kurdish)'))
                    ->maxLength(255),
                TextInput::make('days_allowed')
                    ->label(__('Days Allowed'))
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->default(0),
                Toggle::make('is_paid')
                    ->label(__('Paid Leave'))
                    ->default(true),
                ColorPicker::make('color')
                    ->label(__('Color')),
                Toggle::make('is_active')
                    ->label(__('Active'))
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('days_allowed')
                    ->label(__('Days Allowed'))
                    ->sortable(),
                IconColumn::make('is_paid')
                    ->label(__('Paid'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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

    public static function getPages(): array
    {
        return [
            'index' => ManageLeaveTypes::route('/'),
        ];
    }
}
