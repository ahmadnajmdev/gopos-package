<?php

namespace Gopos\Filament\Clusters\Hr\Resources\Holidays;

use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Gopos\Enums\HolidayType;
use Gopos\Filament\Clusters\Hr\HrCluster;
use Gopos\Filament\Clusters\Hr\Resources\Holidays\Pages\CreateHoliday;
use Gopos\Filament\Clusters\Hr\Resources\Holidays\Pages\EditHoliday;
use Gopos\Filament\Clusters\Hr\Resources\Holidays\Pages\ListHolidays;
use Gopos\Models\Holiday;

class HolidayResource extends Resource
{
    protected static ?string $cluster = HrCluster::class;

    protected static ?string $model = Holiday::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static ?int $navigationSort = 7;

    protected static bool $isScopedToTenant = false;

    public static function getNavigationGroup(): ?string
    {
        return __('Leave Management');
    }

    public static function getNavigationLabel(): string
    {
        return __('Holidays');
    }

    public static function getLabel(): string
    {
        return __('Holiday');
    }

    public static function getPluralLabel(): string
    {
        return __('Holidays');
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
                DatePicker::make('date')
                    ->label(__('Date'))
                    ->required(),
                Select::make('type')
                    ->label(__('Type'))
                    ->options(HolidayType::class)
                    ->default(HolidayType::Public)
                    ->required(),
                Toggle::make('is_recurring')
                    ->label(__('Recurring Yearly'))
                    ->default(false),
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
                TextColumn::make('date')
                    ->label(__('Date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge()
                    ->sortable(),
                IconColumn::make('is_recurring')
                    ->label(__('Recurring'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('Type'))
                    ->options(HolidayType::class),
            ])
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHolidays::route('/'),
            'create' => CreateHoliday::route('/create'),
            'edit' => EditHoliday::route('/{record}/edit'),
        ];
    }
}
