<?php

namespace Gopos\Filament\Clusters\Hr\Resources\Departments;

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
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Gopos\Filament\Clusters\Hr\HrCluster;
use Gopos\Filament\Clusters\Hr\Resources\Departments\Pages\CreateDepartment;
use Gopos\Filament\Clusters\Hr\Resources\Departments\Pages\EditDepartment;
use Gopos\Filament\Clusters\Hr\Resources\Departments\Pages\ListDepartments;
use Gopos\Models\Department;

class DepartmentResource extends Resource
{
    protected static ?string $cluster = HrCluster::class;

    protected static ?string $model = Department::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('Organization');
    }

    public static function getNavigationLabel(): string
    {
        return __('Departments');
    }

    public static function getLabel(): string
    {
        return __('Department');
    }

    public static function getPluralLabel(): string
    {
        return __('Departments');
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
                Select::make('parent_id')
                    ->label(__('Parent Department'))
                    ->relationship('parent', 'name')
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
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('parent.name')
                    ->label(__('Parent Department'))
                    ->sortable()
                    ->placeholder('-'),
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
                QueryBuilder::make()
                    ->constraints([
                        TextConstraint::make('name')
                            ->label(__('Name')),
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
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDepartments::route('/'),
            'create' => CreateDepartment::route('/create'),
            'edit' => EditDepartment::route('/{record}/edit'),
        ];
    }
}
