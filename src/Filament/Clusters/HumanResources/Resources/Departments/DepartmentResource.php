<?php

namespace Gopos\Filament\Clusters\HumanResources\Resources\Departments;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Gopos\Filament\Clusters\HumanResources\HumanResourcesCluster;
use Gopos\Filament\Clusters\HumanResources\Resources\Departments\Pages\CreateDepartment;
use Gopos\Filament\Clusters\HumanResources\Resources\Departments\Pages\EditDepartment;
use Gopos\Filament\Clusters\HumanResources\Resources\Departments\Pages\ListDepartments;
use Gopos\Models\CostCenter;
use Gopos\Models\Department;
use Gopos\Models\User;

class DepartmentResource extends Resource
{
    protected static ?string $cluster = HumanResourcesCluster::class;

    protected static ?string $model = Department::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('Human Resources');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Department Information'))
                    ->schema([
                        TextInput::make('code')
                            ->label(__('Code'))
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true),
                        Select::make('parent_id')
                            ->label(__('Parent Department'))
                            ->options(function ($record) {
                                $excludeIds = $record ? collect([$record->id])->merge($record->getAllDescendants()->pluck('id')) : collect();

                                return Department::whereNotIn('id', $excludeIds)->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->nullable(),
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
                        Select::make('manager_id')
                            ->label(__('Manager'))
                            ->options(User::where('active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('cost_center_id')
                            ->label(__('Cost Center'))
                            ->options(CostCenter::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Toggle::make('is_active')
                            ->label(__('Active'))
                            ->default(true),
                        Textarea::make('description')
                            ->label(__('Description'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Department Information'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('code')
                                    ->label(__('Code'))
                                    ->badge()
                                    ->color('primary'),
                                TextEntry::make('name')
                                    ->label(__('Name'))
                                    ->icon('heroicon-o-building-office-2'),
                                IconEntry::make('is_active')
                                    ->label(__('Status'))
                                    ->boolean(),
                            ]),
                    ]),
                Section::make(__('Localized Names'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('name')
                                    ->label(__('English')),
                                TextEntry::make('name_ar')
                                    ->label(__('Arabic'))
                                    ->placeholder(__('Not specified')),
                                TextEntry::make('name_ckb')
                                    ->label(__('Kurdish'))
                                    ->placeholder(__('Not specified')),
                            ]),
                    ])
                    ->collapsible(),
                Section::make(__('Organization'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('parent.name')
                                    ->label(__('Parent Department'))
                                    ->placeholder(__('No parent'))
                                    ->icon('heroicon-o-arrow-up-circle'),
                                TextEntry::make('manager.name')
                                    ->label(__('Manager'))
                                    ->placeholder(__('No manager assigned'))
                                    ->icon('heroicon-o-user'),
                                TextEntry::make('costCenter.name')
                                    ->label(__('Cost Center'))
                                    ->placeholder(__('Not assigned'))
                                    ->icon('heroicon-o-currency-dollar'),
                            ]),
                    ]),
                Section::make(__('Statistics'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('employees_count')
                                    ->label(__('Total Employees'))
                                    ->getStateUsing(fn ($record) => $record->employees()->count())
                                    ->badge()
                                    ->color('success')
                                    ->icon('heroicon-o-users'),
                                TextEntry::make('positions_count')
                                    ->label(__('Total Positions'))
                                    ->getStateUsing(fn ($record) => $record->positions()->count())
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-o-briefcase'),
                            ]),
                    ]),
                Section::make(__('Additional Information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('description')
                                    ->label(__('Description'))
                                    ->placeholder(__('No description'))
                                    ->columnSpanFull(),
                                TextEntry::make('created_at')
                                    ->label(__('Created At'))
                                    ->dateTime(),
                                TextEntry::make('updated_at')
                                    ->label(__('Last Updated'))
                                    ->dateTime(),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
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
                TextColumn::make('parent.name')
                    ->label(__('Parent'))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('manager.name')
                    ->label(__('Manager'))
                    ->toggleable(),
                TextColumn::make('employees_count')
                    ->label(__('Employees'))
                    ->counts('employees')
                    ->sortable(),
                TextColumn::make('positions_count')
                    ->label(__('Positions'))
                    ->counts('positions')
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('parent_id')
                    ->label(__('Parent Department'))
                    ->options(Department::whereNotNull('parent_id')->pluck('name', 'parent_id')->unique()),
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
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getLabel(): string
    {
        return __('Department');
    }

    public static function getPluralLabel(): string
    {
        return __('Departments');
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
