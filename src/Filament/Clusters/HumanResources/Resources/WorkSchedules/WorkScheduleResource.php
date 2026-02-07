<?php

namespace Gopos\Filament\Clusters\HumanResources\Resources\WorkSchedules;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
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
use Gopos\Filament\Clusters\HumanResources\Resources\WorkSchedules\Pages\CreateWorkSchedule;
use Gopos\Filament\Clusters\HumanResources\Resources\WorkSchedules\Pages\EditWorkSchedule;
use Gopos\Filament\Clusters\HumanResources\Resources\WorkSchedules\Pages\ListWorkSchedules;
use Gopos\Models\WorkSchedule;

class WorkScheduleResource extends Resource
{
    protected static ?string $cluster = HumanResourcesCluster::class;

    protected static ?string $model = WorkSchedule::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static ?int $navigationSort = 11;

    public static function getNavigationGroup(): ?string
    {
        return __('Human Resources');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Schedule Information'))
                    ->schema([
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
                        Toggle::make('is_default')
                            ->label(__('Default Schedule')),
                        Toggle::make('is_active')
                            ->label(__('Active'))
                            ->default(true),
                    ])->columns(2),
                Section::make(__('Working Hours'))
                    ->schema([
                        TimePicker::make('work_start_time')
                            ->label(__('Work Start Time'))
                            ->required()
                            ->seconds(false),
                        TimePicker::make('work_end_time')
                            ->label(__('Work End Time'))
                            ->required()
                            ->seconds(false)
                            ->after('work_start_time'),
                        TimePicker::make('break_start_time')
                            ->label(__('Break Start Time'))
                            ->seconds(false)
                            ->afterOrEqual('work_start_time'),
                        TimePicker::make('break_end_time')
                            ->label(__('Break End Time'))
                            ->seconds(false)
                            ->afterOrEqual('break_start_time'),
                        TextInput::make('working_hours')
                            ->label(__('Working Hours per Day'))
                            ->numeric()
                            ->required()
                            ->default(8),
                    ])->columns(2),
                Section::make(__('Tolerance Settings'))
                    ->schema([
                        TextInput::make('late_tolerance_minutes')
                            ->label(__('Late Tolerance (Minutes)'))
                            ->numeric()
                            ->default(15)
                            ->helperText(__('Grace period before marking as late')),
                        TextInput::make('early_leave_tolerance_minutes')
                            ->label(__('Early Leave Tolerance (Minutes)'))
                            ->numeric()
                            ->default(15)
                            ->helperText(__('Grace period before marking as early leave')),
                    ])->columns(2),
                Section::make(__('Working Days'))
                    ->schema([
                        CheckboxList::make('working_days')
                            ->label('')
                            ->options([
                                0 => __('Sunday'),
                                1 => __('Monday'),
                                2 => __('Tuesday'),
                                3 => __('Wednesday'),
                                4 => __('Thursday'),
                                5 => __('Friday'),
                                6 => __('Saturday'),
                            ])
                            ->columns(4)
                            ->default([0, 1, 2, 3, 4]),
                    ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        $dayNames = [
            0 => __('Sunday'),
            1 => __('Monday'),
            2 => __('Tuesday'),
            3 => __('Wednesday'),
            4 => __('Thursday'),
            5 => __('Friday'),
            6 => __('Saturday'),
        ];

        return $schema
            ->components([
                Section::make(__('Schedule Information'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('name')
                                    ->label(__('Name'))
                                    ->icon('heroicon-o-clock'),
                                IconEntry::make('is_default')
                                    ->label(__('Default Schedule'))
                                    ->boolean(),
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
                Section::make(__('Working Hours'))
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('work_start_time')
                                    ->label(__('Start Time'))
                                    ->time('H:i')
                                    ->icon('heroicon-o-play')
                                    ->weight(FontWeight::Bold)
                                    ->color('success'),
                                TextEntry::make('work_end_time')
                                    ->label(__('End Time'))
                                    ->time('H:i')
                                    ->icon('heroicon-o-stop')
                                    ->weight(FontWeight::Bold)
                                    ->color('danger'),
                                TextEntry::make('working_hours')
                                    ->label(__('Hours per Day'))
                                    ->numeric(locale: 'en')
                                    ->suffix(' '.__('hrs'))
                                    ->badge()
                                    ->color('primary'),
                                TextEntry::make('employees_count')
                                    ->label(__('Employees'))
                                    ->getStateUsing(fn ($record) => $record->employees()->count())
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-o-users'),
                            ]),
                    ]),
                Section::make(__('Break Time'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('break_start_time')
                                    ->label(__('Break Start'))
                                    ->time('H:i')
                                    ->placeholder(__('No break configured')),
                                TextEntry::make('break_end_time')
                                    ->label(__('Break End'))
                                    ->time('H:i')
                                    ->placeholder(__('No break configured')),
                            ]),
                    ])
                    ->collapsible(),
                Section::make(__('Tolerance Settings'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('late_tolerance_minutes')
                                    ->label(__('Late Tolerance'))
                                    ->numeric(locale: 'en')
                                    ->suffix(' '.__('minutes'))
                                    ->icon('heroicon-o-clock'),
                                TextEntry::make('early_leave_tolerance_minutes')
                                    ->label(__('Early Leave Tolerance'))
                                    ->numeric(locale: 'en')
                                    ->suffix(' '.__('minutes'))
                                    ->icon('heroicon-o-clock'),
                            ]),
                    ]),
                Section::make(__('Working Days'))
                    ->schema([
                        TextEntry::make('working_days')
                            ->label(__('Days'))
                            ->badge()
                            ->color('success')
                            ->formatStateUsing(function ($state) use ($dayNames) {
                                if (is_array($state)) {
                                    return collect($state)->map(fn ($day) => $dayNames[$day] ?? $day)->join(', ');
                                }

                                return $state;
                            }),
                    ]),
                Section::make(__('Additional Information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
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
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('work_start_time')
                    ->label(__('Start Time'))
                    ->time('H:i'),
                TextColumn::make('work_end_time')
                    ->label(__('End Time'))
                    ->time('H:i'),
                TextColumn::make('working_hours')
                    ->label(__('Hours/Day'))
                    ->suffix(' hrs'),
                TextColumn::make('late_tolerance_minutes')
                    ->label(__('Late Tolerance'))
                    ->suffix(' min')
                    ->toggleable(),
                TextColumn::make('employees_count')
                    ->label(__('Employees'))
                    ->counts('employees')
                    ->sortable(),
                IconColumn::make('is_default')
                    ->label(__('Default'))
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),
            ])
            ->filters([
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
        return __('Work Schedule');
    }

    public static function getPluralLabel(): string
    {
        return __('Work Schedules');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkSchedules::route('/'),
            'create' => CreateWorkSchedule::route('/create'),
            'edit' => EditWorkSchedule::route('/{record}/edit'),
        ];
    }
}
