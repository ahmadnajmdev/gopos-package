<?php

namespace Gopos\Filament\Clusters\HumanResources\Resources\PayrollComponents;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Gopos\Filament\Clusters\HumanResources\HumanResourcesCluster;
use Gopos\Filament\Clusters\HumanResources\Resources\PayrollComponents\Pages\CreatePayrollComponent;
use Gopos\Filament\Clusters\HumanResources\Resources\PayrollComponents\Pages\EditPayrollComponent;
use Gopos\Filament\Clusters\HumanResources\Resources\PayrollComponents\Pages\ListPayrollComponents;
use Gopos\Models\PayrollComponent;

class PayrollComponentResource extends Resource
{
    protected static ?string $cluster = HumanResourcesCluster::class;

    protected static ?string $model = PayrollComponent::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calculator';

    protected static ?int $navigationSort = 8;

    public static function getNavigationGroup(): ?string
    {
        return __('Human Resources');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Component Information'))
                    ->schema([
                        TextInput::make('code')
                            ->label(__('Code'))
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true),
                        Select::make('type')
                            ->label(__('Type'))
                            ->options([
                                'earning' => __('Earning'),
                                'deduction' => __('Deduction'),
                            ])
                            ->required(),
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
                        TextInput::make('display_order')
                            ->label(__('Display Order'))
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_active')
                            ->label(__('Active'))
                            ->default(true),
                        Toggle::make('is_mandatory')
                            ->label(__('Mandatory')),
                    ])->columns(2),
                Section::make(__('Calculation'))
                    ->schema([
                        Select::make('calculation_type')
                            ->label(__('Calculation Type'))
                            ->options([
                                'fixed' => __('Fixed Amount'),
                                'percentage' => __('Percentage of Salary'),
                                'formula' => __('Custom Formula'),
                            ])
                            ->required()
                            ->default('fixed')
                            ->live(),
                        TextInput::make('default_amount')
                            ->label(__('Default Amount'))
                            ->numeric()
                            ->minValue(0)
                            ->prefix('$')
                            ->visible(fn ($get) => $get('calculation_type') === 'fixed'),
                        TextInput::make('percentage')
                            ->label(__('Percentage'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->visible(fn ($get) => $get('calculation_type') === 'percentage'),
                        TextInput::make('min_amount')
                            ->label(__('Minimum Amount'))
                            ->numeric()
                            ->minValue(0)
                            ->prefix('$'),
                        TextInput::make('max_amount')
                            ->label(__('Maximum Amount'))
                            ->numeric()
                            ->minValue(0)
                            ->gte('min_amount')
                            ->prefix('$'),
                    ])->columns(2),
                Section::make(__('Tax Settings'))
                    ->schema([
                        Toggle::make('is_taxable')
                            ->label(__('Taxable'))
                            ->default(true),
                        Toggle::make('applies_to_all')
                            ->label(__('Applies to All Employees'))
                            ->default(false),
                    ])->columns(2),
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
                TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'earning' => __('Earning'),
                        'deduction' => __('Deduction'),
                        default => $state,
                    })
                    ->colors([
                        'success' => 'earning',
                        'danger' => 'deduction',
                    ]),
                TextColumn::make('calculation_type')
                    ->label(__('Calculation'))
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'fixed' => __('Fixed'),
                        'percentage' => __('Percentage'),
                        'formula' => __('Formula'),
                        default => $state,
                    }),
                TextColumn::make('default_amount')
                    ->label(__('Amount'))
                    ->money('USD')
                    ->toggleable(),
                TextColumn::make('percentage')
                    ->label(__('Percentage'))
                    ->suffix('%')
                    ->toggleable(),
                IconColumn::make('is_mandatory')
                    ->label(__('Mandatory'))
                    ->boolean(),
                IconColumn::make('is_taxable')
                    ->label(__('Taxable'))
                    ->boolean()
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('Type'))
                    ->options([
                        'earning' => __('Earning'),
                        'deduction' => __('Deduction'),
                    ]),
                SelectFilter::make('is_active')
                    ->label(__('Status'))
                    ->options([
                        '1' => __('Active'),
                        '0' => __('Inactive'),
                    ]),
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
            ])
            ->reorderable('display_order')
            ->defaultSort('display_order');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getLabel(): string
    {
        return __('Payroll Component');
    }

    public static function getPluralLabel(): string
    {
        return __('Payroll Components');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayrollComponents::route('/'),
            'create' => CreatePayrollComponent::route('/create'),
            'edit' => EditPayrollComponent::route('/{record}/edit'),
        ];
    }
}
