<?php

namespace Gopos\Filament\Clusters\Accounting\Resources\TaxCodes;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Gopos\Filament\Clusters\Accounting\AccountingCluster;
use Gopos\Filament\Clusters\Accounting\Resources\TaxCodes\Pages\ManageTaxCodes;
use Gopos\Models\TaxCode;

class TaxCodeResource extends Resource
{
    protected static ?string $cluster = AccountingCluster::class;

    protected static ?string $model = TaxCode::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?int $navigationSort = 20;

    public static function getNavigationGroup(): ?string
    {
        return __('Accounting');
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
                TextInput::make('code')
                    ->label(__('Code'))
                    ->required()
                    ->maxLength(20)
                    ->unique(ignoreRecord: true),
                TextInput::make('rate')
                    ->label(__('Rate (%)'))
                    ->required()
                    ->numeric()
                    ->suffix('%')
                    ->step(0.01)
                    ->minValue(0)
                    ->maxValue(100)
                    ->helperText(__('Enter the tax rate as a percentage (e.g., 15 for 15%)'))
                    ->dehydrateStateUsing(fn ($state) => $state / 100)
                    ->formatStateUsing(fn ($state) => $state * 100),
                Select::make('type')
                    ->label(__('Type'))
                    ->options([
                        'exclusive' => __('Exclusive (added to price)'),
                        'inclusive' => __('Inclusive (included in price)'),
                    ])
                    ->default('exclusive')
                    ->required(),
                CheckboxList::make('applies_to')
                    ->label(__('Applies To'))
                    ->options([
                        'sales' => __('Sales'),
                        'purchases' => __('Purchases'),
                        'both' => __('Both'),
                    ])
                    ->default(['both']),
                Checkbox::make('is_active')
                    ->label(__('Active'))
                    ->default(true),
                Textarea::make('description')
                    ->label(__('Description'))
                    ->rows(2),
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
                TextColumn::make('formatted_rate')
                    ->label(__('Rate'))
                    ->sortable(query: fn ($query, $direction) => $query->orderBy('rate', $direction)),
                TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'exclusive' ? __('Exclusive') : __('Inclusive'))
                    ->color(fn ($state) => $state === 'exclusive' ? 'info' : 'warning'),
                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('Type'))
                    ->options([
                        'exclusive' => __('Exclusive'),
                        'inclusive' => __('Inclusive'),
                    ]),
                SelectFilter::make('is_active')
                    ->label(__('Status'))
                    ->options([
                        '1' => __('Active'),
                        '0' => __('Inactive'),
                    ]),
            ])
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
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

    public static function getLabel(): string
    {
        return __('Tax Code');
    }

    public static function getPluralLabel(): string
    {
        return __('Tax Codes');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTaxCodes::route('/'),
        ];
    }
}
