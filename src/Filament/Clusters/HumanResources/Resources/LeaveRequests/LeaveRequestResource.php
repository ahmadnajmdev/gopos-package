<?php

namespace Gopos\Filament\Clusters\HumanResources\Resources\LeaveRequests;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Gopos\Filament\Clusters\HumanResources\HumanResourcesCluster;
use Gopos\Filament\Clusters\HumanResources\Resources\LeaveRequests\Pages\CreateLeaveRequest;
use Gopos\Filament\Clusters\HumanResources\Resources\LeaveRequests\Pages\ListLeaveRequests;
use Gopos\Filament\Clusters\HumanResources\Resources\LeaveRequests\Pages\ViewLeaveRequest;
use Gopos\Models\Employee;
use Gopos\Models\LeaveRequest;
use Gopos\Models\LeaveType;

class LeaveRequestResource extends Resource
{
    protected static ?string $cluster = HumanResourcesCluster::class;

    protected static ?string $model = LeaveRequest::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar';

    protected static ?int $navigationSort = 6;

    public static function getNavigationGroup(): ?string
    {
        return __('Human Resources');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Leave Request'))
                    ->schema([
                        Select::make('employee_id')
                            ->label(__('Employee'))
                            ->options(Employee::active()->get()->pluck('display_name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('leave_type_id')
                            ->label(__('Leave Type'))
                            ->options(LeaveType::active()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        DatePicker::make('start_date')
                            ->label(__('Start Date'))
                            ->required()
                            ->minDate(now()),
                        DatePicker::make('end_date')
                            ->label(__('End Date'))
                            ->required()
                            ->minDate(fn ($get) => $get('start_date')),
                        Toggle::make('is_half_day')
                            ->label(__('Half Day'))
                            ->live(),
                        Select::make('half_day_type')
                            ->label(__('Half Day Type'))
                            ->options([
                                'morning' => __('Morning'),
                                'afternoon' => __('Afternoon'),
                            ])
                            ->visible(fn ($get) => $get('is_half_day')),
                        Textarea::make('reason')
                            ->label(__('Reason'))
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        FileUpload::make('attachment')
                            ->label(__('Attachment'))
                            ->directory('leave-attachments')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(5120)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.full_name')
                    ->label(__('Employee'))
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                TextColumn::make('leaveType.name')
                    ->label(__('Leave Type'))
                    ->sortable(),
                TextColumn::make('start_date')
                    ->label(__('Start Date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label(__('End Date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('days')
                    ->label(__('Days'))
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => __('Pending'),
                        'approved' => __('Approved'),
                        'rejected' => __('Rejected'),
                        'cancelled' => __('Cancelled'),
                        default => $state,
                    })
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'gray' => 'cancelled',
                    ]),
                TextColumn::make('requested_at')
                    ->label(__('Requested'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('approver.name')
                    ->label(__('Approved By'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'pending' => __('Pending'),
                        'approved' => __('Approved'),
                        'rejected' => __('Rejected'),
                        'cancelled' => __('Cancelled'),
                    ]),
                SelectFilter::make('leave_type_id')
                    ->label(__('Leave Type'))
                    ->options(LeaveType::active()->pluck('name', 'id')),
                SelectFilter::make('employee_id')
                    ->label(__('Employee'))
                    ->options(Employee::active()->get()->pluck('display_name', 'id'))
                    ->searchable()
                    ->preload(),
            ])
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                ViewAction::make(),
                Action::make('approve')
                    ->label(__('Approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (LeaveRequest $record) => $record->status === 'pending')
                    ->action(fn (LeaveRequest $record) => $record->approve(auth()->id())),
                Action::make('reject')
                    ->label(__('Reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label(__('Rejection Reason'))
                            ->required(),
                    ])
                    ->visible(fn (LeaveRequest $record) => $record->status === 'pending')
                    ->action(fn (LeaveRequest $record, array $data) => $record->reject(auth()->id(), $data['rejection_reason'])
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('requested_at', 'desc');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Leave Request Details'))
                    ->schema([
                        TextEntry::make('employee.full_name')
                            ->label(__('Employee')),
                        TextEntry::make('leaveType.name')
                            ->label(__('Leave Type')),
                        TextEntry::make('start_date')
                            ->label(__('Start Date'))
                            ->date(),
                        TextEntry::make('end_date')
                            ->label(__('End Date'))
                            ->date(),
                        TextEntry::make('days')
                            ->label(__('Total Days')),
                        TextEntry::make('status')
                            ->label(__('Status'))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('reason')
                            ->label(__('Reason'))
                            ->columnSpanFull(),
                    ])->columns(2),
                Section::make(__('Approval Information'))
                    ->schema([
                        TextEntry::make('approver.name')
                            ->label(__('Processed By')),
                        TextEntry::make('approved_at')
                            ->label(__('Processed At'))
                            ->dateTime(),
                        TextEntry::make('rejection_reason')
                            ->label(__('Rejection Reason'))
                            ->visible(fn ($record) => $record->status === 'rejected'),
                    ])->columns(2)
                    ->visible(fn ($record) => in_array($record->status, ['approved', 'rejected'])),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getLabel(): string
    {
        return __('Leave Request');
    }

    public static function getPluralLabel(): string
    {
        return __('Leave Requests');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLeaveRequests::route('/'),
            'create' => CreateLeaveRequest::route('/create'),
            'view' => ViewLeaveRequest::route('/{record}'),
        ];
    }
}
