<?php

namespace Gopos\Filament\Clusters\HumanResources\Resources\Attendances;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Gopos\Filament\Clusters\HumanResources\HumanResourcesCluster;
use Gopos\Filament\Clusters\HumanResources\Resources\Attendances\Pages\CreateAttendance;
use Gopos\Filament\Clusters\HumanResources\Resources\Attendances\Pages\EditAttendance;
use Gopos\Filament\Clusters\HumanResources\Resources\Attendances\Pages\ListAttendances;
use Gopos\Models\Attendance;
use Gopos\Models\Department;
use Gopos\Models\Employee;
use Illuminate\Database\Eloquent\Builder;

class AttendanceResource extends Resource
{
    protected static ?string $cluster = HumanResourcesCluster::class;

    protected static ?string $model = Attendance::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return __('Human Resources');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Attendance Details'))
                    ->schema([
                        Select::make('employee_id')
                            ->label(__('Employee'))
                            ->options(Employee::active()->get()->pluck('display_name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        DatePicker::make('date')
                            ->label(__('Date'))
                            ->required()
                            ->default(now()),
                        Select::make('status')
                            ->label(__('Status'))
                            ->options([
                                'present' => __('Present'),
                                'absent' => __('Absent'),
                                'late' => __('Late'),
                                'half_day' => __('Half Day'),
                                'holiday' => __('Holiday'),
                                'weekend' => __('Weekend'),
                                'leave' => __('On Leave'),
                            ])
                            ->required()
                            ->default('present'),
                        TimePicker::make('clock_in')
                            ->label(__('Clock In'))
                            ->seconds(false),
                        TimePicker::make('clock_out')
                            ->label(__('Clock Out'))
                            ->seconds(false)
                            ->afterOrEqual('clock_in'),
                        TimePicker::make('break_start')
                            ->label(__('Break Start'))
                            ->seconds(false)
                            ->afterOrEqual('clock_in'),
                        TimePicker::make('break_end')
                            ->label(__('Break End'))
                            ->seconds(false)
                            ->afterOrEqual('break_start'),
                        Textarea::make('notes')
                            ->label(__('Notes'))
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label(__('Date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('employee.full_name')
                    ->label(__('Employee'))
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                TextColumn::make('employee.department.name')
                    ->label(__('Department'))
                    ->toggleable(),
                TextColumn::make('clock_in')
                    ->label(__('Clock In'))
                    ->time('H:i'),
                TextColumn::make('clock_out')
                    ->label(__('Clock Out'))
                    ->time('H:i'),
                TextColumn::make('worked_hours')
                    ->label(__('Worked Hours'))
                    ->suffix(' hrs')
                    ->sortable(),
                TextColumn::make('overtime_hours')
                    ->label(__('Overtime'))
                    ->suffix(' hrs')
                    ->toggleable(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'present' => __('Present'),
                        'absent' => __('Absent'),
                        'late' => __('Late'),
                        'half_day' => __('Half Day'),
                        'holiday' => __('Holiday'),
                        'weekend' => __('Weekend'),
                        'leave' => __('On Leave'),
                        default => $state,
                    })
                    ->colors([
                        'success' => 'present',
                        'danger' => 'absent',
                        'warning' => 'late',
                        'info' => 'half_day',
                        'gray' => ['holiday', 'weekend', 'leave'],
                    ]),
                IconColumn::make('is_late')
                    ->label(__('Late'))
                    ->boolean()
                    ->trueIcon('heroicon-o-clock')
                    ->falseIcon('')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('employee_id')
                    ->label(__('Employee'))
                    ->options(Employee::active()->get()->pluck('display_name', 'id'))
                    ->searchable()
                    ->preload(),
                SelectFilter::make('department')
                    ->label(__('Department'))
                    ->options(Department::active()->pluck('name', 'id'))
                    ->query(fn (Builder $query, array $data): Builder => $query->when($data['value'], fn ($q) => $q->whereHas('employee', fn ($eq) => $eq->where('department_id', $data['value'])
                    )
                    )
                    ),
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'present' => __('Present'),
                        'absent' => __('Absent'),
                        'late' => __('Late'),
                        'half_day' => __('Half Day'),
                        'leave' => __('On Leave'),
                    ]),
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('from')
                            ->label(__('From Date')),
                        DatePicker::make('until')
                            ->label(__('To Date')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn ($q) => $q->whereDate('date', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->whereDate('date', '<=', $data['until']));
                    }),
            ])
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Attendance Details'))
                    ->schema([
                        TextEntry::make('employee.full_name')
                            ->label(__('Employee')),
                        TextEntry::make('date')
                            ->label(__('Date'))
                            ->date(),
                        TextEntry::make('status')
                            ->label(__('Status'))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'present' => 'success',
                                'absent' => 'danger',
                                'late' => 'warning',
                                default => 'gray',
                            }),
                        TextEntry::make('clock_in')
                            ->label(__('Clock In'))
                            ->time('H:i'),
                        TextEntry::make('clock_out')
                            ->label(__('Clock Out'))
                            ->time('H:i'),
                        TextEntry::make('worked_hours')
                            ->label(__('Worked Hours'))
                            ->suffix(' hrs'),
                        TextEntry::make('overtime_hours')
                            ->label(__('Overtime Hours'))
                            ->suffix(' hrs'),
                        TextEntry::make('late_minutes')
                            ->label(__('Late Minutes'))
                            ->suffix(' min')
                            ->visible(fn ($record) => $record->is_late),
                    ])->columns(2),
                Section::make(__('Location Info'))
                    ->schema([
                        TextEntry::make('clock_in_location')
                            ->label(__('Clock In Location')),
                        TextEntry::make('clock_out_location')
                            ->label(__('Clock Out Location')),
                        TextEntry::make('clock_in_ip')
                            ->label(__('Clock In IP')),
                        TextEntry::make('clock_out_ip')
                            ->label(__('Clock Out IP')),
                    ])->columns(2)
                    ->collapsed(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getLabel(): string
    {
        return __('Attendance');
    }

    public static function getPluralLabel(): string
    {
        return __('Attendance Records');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAttendances::route('/'),
            'create' => CreateAttendance::route('/create'),
            'edit' => EditAttendance::route('/{record}/edit'),
        ];
    }
}
