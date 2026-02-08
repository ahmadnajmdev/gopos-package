<?php

namespace Gopos\Filament\Clusters\Hr\Resources\Positions;

use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\BooleanConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Gopos\Filament\Clusters\Hr\HrCluster;
use Gopos\Filament\Clusters\Hr\Resources\Positions\Pages\CreatePosition;
use Gopos\Filament\Clusters\Hr\Resources\Positions\Pages\EditPosition;
use Gopos\Filament\Clusters\Hr\Resources\Positions\Pages\ListPositions;
use Gopos\Models\Position;

class PositionResource extends Resource
{
    protected static ?string $cluster = HrCluster::class;

    protected static ?string $model = Position::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('Organization');
    }

    public static function getNavigationLabel(): string
    {
        return __('Positions');
    }

    public static function getLabel(): string
    {
        return __('Position');
    }

    public static function getPluralLabel(): string
    {
        return __('Positions');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label(__('Title'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('title_ar')
                    ->label(__('Title (Arabic)'))
                    ->maxLength(255),
                TextInput::make('title_ckb')
                    ->label(__('Title (Kurdish)'))
                    ->maxLength(255),
                Select::make('department_id')
                    ->label(__('Department'))
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label(__('Name'))
                            ->required()
                            ->maxLength(255),
                        Toggle::make('is_active')
                            ->label(__('Active'))
                            ->default(true),
                    ]),
                TextInput::make('min_salary')
                    ->label(__('Minimum Salary'))
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0),
                TextInput::make('max_salary')
                    ->label(__('Maximum Salary'))
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0),
                Textarea::make('description')
                    ->label(__('Description'))
                    ->rows(3),
                Toggle::make('is_active')
                    ->label(__('Active'))
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('Title'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('department.name')
                    ->label(__('Department'))
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('min_salary')
                    ->label(__('Min Salary'))
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('max_salary')
                    ->label(__('Max Salary'))
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('employees_count')
                    ->label(__('Employees'))
                    ->counts('employees')
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
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('Active')),
                SelectFilter::make('department_id')
                    ->label(__('Department'))
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload(),
                QueryBuilder::make()
                    ->constraints([
                        TextConstraint::make('title')
                            ->label(__('Title')),
                        NumberConstraint::make('min_salary')
                            ->label(__('Min Salary')),
                        NumberConstraint::make('max_salary')
                            ->label(__('Max Salary')),
                        BooleanConstraint::make('is_active')
                            ->label(__('Active')),
                    ]),
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
            ->defaultSort('title');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPositions::route('/'),
            'create' => CreatePosition::route('/create'),
            'edit' => EditPosition::route('/{record}/edit'),
        ];
    }
}
