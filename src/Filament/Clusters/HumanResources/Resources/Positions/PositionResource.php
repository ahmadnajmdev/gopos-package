<?php

namespace Gopos\Filament\Clusters\HumanResources\Resources\Positions;

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
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Gopos\Filament\Clusters\HumanResources\HumanResourcesCluster;
use Gopos\Filament\Clusters\HumanResources\Resources\Positions\Pages\CreatePosition;
use Gopos\Filament\Clusters\HumanResources\Resources\Positions\Pages\EditPosition;
use Gopos\Filament\Clusters\HumanResources\Resources\Positions\Pages\ListPositions;
use Gopos\Models\Department;
use Gopos\Models\Position;

class PositionResource extends Resource
{
    protected static ?string $cluster = HumanResourcesCluster::class;

    protected static ?string $model = Position::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('Human Resources');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Position Information'))
                    ->schema([
                        TextInput::make('code')
                            ->label(__('Code'))
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true),
                        Select::make('department_id')
                            ->label(__('Department'))
                            ->options(Department::active()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('title')
                            ->label(__('Title (English)'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('title_ar')
                            ->label(__('Title (Arabic)'))
                            ->maxLength(255),
                        TextInput::make('title_ckb')
                            ->label(__('Title (Kurdish)'))
                            ->maxLength(255),
                        TextInput::make('level')
                            ->label(__('Level'))
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(10)
                            ->default(1),
                        Toggle::make('is_active')
                            ->label(__('Active'))
                            ->default(true)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label(__('Description'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
                Section::make(__('Salary Range'))
                    ->schema([
                        TextInput::make('min_salary')
                            ->label(__('Minimum Salary'))
                            ->numeric()
                            ->prefix('$'),
                        TextInput::make('max_salary')
                            ->label(__('Maximum Salary'))
                            ->numeric()
                            ->prefix('$')
                            ->gte('min_salary'),
                    ])->columns(2),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Position Information'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('code')
                                    ->label(__('Code'))
                                    ->badge()
                                    ->color('primary'),
                                TextEntry::make('title')
                                    ->label(__('Title'))
                                    ->icon('heroicon-o-briefcase'),
                                IconEntry::make('is_active')
                                    ->label(__('Status'))
                                    ->boolean(),
                            ]),
                    ]),
                Section::make(__('Localized Titles'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('title')
                                    ->label(__('English')),
                                TextEntry::make('title_ar')
                                    ->label(__('Arabic'))
                                    ->placeholder(__('Not specified')),
                                TextEntry::make('title_ckb')
                                    ->label(__('Kurdish'))
                                    ->placeholder(__('Not specified')),
                            ]),
                    ])
                    ->collapsible(),
                Section::make(__('Department & Level'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('department.name')
                                    ->label(__('Department'))
                                    ->icon('heroicon-o-building-office-2'),
                                TextEntry::make('level')
                                    ->label(__('Level'))
                                    ->badge()
                                    ->color('info'),
                            ]),
                    ]),
                Section::make(__('Salary Range'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('min_salary')
                                    ->label(__('Minimum Salary'))
                                    ->numeric(locale: 'en')
                                    ->prefix('$')
                                    ->placeholder(__('Not set'))
                                    ->weight(FontWeight::Bold)
                                    ->color('warning'),
                                TextEntry::make('max_salary')
                                    ->label(__('Maximum Salary'))
                                    ->numeric(locale: 'en')
                                    ->prefix('$')
                                    ->placeholder(__('Not set'))
                                    ->weight(FontWeight::Bold)
                                    ->color('success'),
                                TextEntry::make('employees_count')
                                    ->label(__('Current Employees'))
                                    ->getStateUsing(fn ($record) => $record->employees()->count())
                                    ->badge()
                                    ->color('primary')
                                    ->icon('heroicon-o-users'),
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
                TextColumn::make('title')
                    ->label(__('Title'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('department.name')
                    ->label(__('Department'))
                    ->sortable(),
                TextColumn::make('level')
                    ->label(__('Level'))
                    ->sortable()
                    ->badge(),
                TextColumn::make('salary_range')
                    ->label(__('Salary Range'))
                    ->toggleable(),
                TextColumn::make('employees_count')
                    ->label(__('Employees'))
                    ->counts('employees')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('department_id')
                    ->label(__('Department'))
                    ->options(Department::active()->pluck('name', 'id')),
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
        return __('Position');
    }

    public static function getPluralLabel(): string
    {
        return __('Positions');
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
