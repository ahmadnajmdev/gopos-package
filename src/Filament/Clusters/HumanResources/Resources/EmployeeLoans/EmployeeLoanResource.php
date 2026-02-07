<?php

namespace Gopos\Filament\Clusters\HumanResources\Resources\EmployeeLoans;

use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Gopos\Filament\Clusters\HumanResources\HumanResourcesCluster;
use Gopos\Filament\Clusters\HumanResources\Resources\EmployeeLoans\Pages\CreateEmployeeLoan;
use Gopos\Filament\Clusters\HumanResources\Resources\EmployeeLoans\Pages\ListEmployeeLoans;
use Gopos\Filament\Clusters\HumanResources\Resources\EmployeeLoans\Pages\ViewEmployeeLoan;
use Gopos\Models\Employee;
use Gopos\Models\EmployeeLoan;

class EmployeeLoanResource extends Resource
{
    protected static ?string $cluster = HumanResourcesCluster::class;

    protected static ?string $model = EmployeeLoan::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected static ?int $navigationSort = 9;

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
                Section::make(__('Loan Information'))
                    ->schema([
                        Select::make('employee_id')
                            ->label(__('Employee'))
                            ->options(Employee::active()->get()->pluck('display_name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('loan_type')
                            ->label(__('Loan Type'))
                            ->options([
                                'salary_advance' => __('Salary Advance'),
                                'personal_loan' => __('Personal Loan'),
                                'emergency_loan' => __('Emergency Loan'),
                            ])
                            ->required(),
                        TextInput::make('loan_amount')
                            ->label(__('Loan Amount'))
                            ->numeric()
                            ->minValue(0.01)
                            ->prefix('$')
                            ->required(),
                        TextInput::make('interest_rate')
                            ->label(__('Interest Rate'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->default(0),
                        TextInput::make('installments')
                            ->label(__('Number of Installments'))
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(60),
                        DatePicker::make('start_date')
                            ->label(__('Start Date'))
                            ->required()
                            ->default(now()->startOfMonth()->addMonth()),
                        Textarea::make('reason')
                            ->label(__('Reason'))
                            ->rows(2)
                            ->columnSpanFull(),
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
                TextColumn::make('loan_number')
                    ->label(__('Loan #'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee.full_name')
                    ->label(__('Employee'))
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                TextColumn::make('loan_type')
                    ->label(__('Type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'salary_advance' => __('Salary Advance'),
                        'personal_loan' => __('Personal Loan'),
                        'emergency_loan' => __('Emergency Loan'),
                        default => $state,
                    }),
                TextColumn::make('loan_amount')
                    ->label(__('Amount'))
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('installment_amount')
                    ->label(__('Monthly'))
                    ->money('USD'),
                TextColumn::make('remaining_amount')
                    ->label(__('Remaining'))
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('paid_installments')
                    ->label(__('Paid'))
                    ->formatStateUsing(fn ($record) => $record->paid_installments.'/'.$record->installments),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => __('Pending'),
                        'approved' => __('Approved'),
                        'active' => __('Active'),
                        'completed' => __('Completed'),
                        'rejected' => __('Rejected'),
                        'cancelled' => __('Cancelled'),
                        default => $state,
                    })
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'approved',
                        'success' => 'active',
                        'primary' => 'completed',
                        'danger' => ['rejected', 'cancelled'],
                    ]),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'pending' => __('Pending'),
                        'approved' => __('Approved'),
                        'active' => __('Active'),
                        'completed' => __('Completed'),
                        'rejected' => __('Rejected'),
                    ]),
                SelectFilter::make('loan_type')
                    ->label(__('Loan Type'))
                    ->options([
                        'salary_advance' => __('Salary Advance'),
                        'personal_loan' => __('Personal Loan'),
                        'emergency_loan' => __('Emergency Loan'),
                    ]),
                SelectFilter::make('employee_id')
                    ->label(__('Employee'))
                    ->options(Employee::active()->get()->pluck('display_name', 'id'))
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('approve')
                    ->label(__('Approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (EmployeeLoan $record) => $record->status === 'pending')
                    ->action(function (EmployeeLoan $record) {
                        $record->approve(auth()->id());
                        $record->calculateLoan();
                        $record->activate();
                    }),
                Action::make('reject')
                    ->label(__('Reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (EmployeeLoan $record) => $record->status === 'pending')
                    ->action(fn (EmployeeLoan $record) => $record->update(['status' => 'rejected'])),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Loan Details'))
                    ->schema([
                        TextEntry::make('loan_number')
                            ->label(__('Loan Number')),
                        TextEntry::make('employee.full_name')
                            ->label(__('Employee')),
                        TextEntry::make('loan_type')
                            ->label(__('Loan Type'))
                            ->badge(),
                        TextEntry::make('status')
                            ->label(__('Status'))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'approved' => 'info',
                                'active' => 'success',
                                'completed' => 'primary',
                                default => 'danger',
                            }),
                    ])->columns(2),
                Section::make(__('Financial Details'))
                    ->schema([
                        TextEntry::make('loan_amount')
                            ->label(__('Principal Amount'))
                            ->money('USD'),
                        TextEntry::make('interest_rate')
                            ->label(__('Interest Rate'))
                            ->suffix('%'),
                        TextEntry::make('total_amount')
                            ->label(__('Total Amount'))
                            ->money('USD'),
                        TextEntry::make('installment_amount')
                            ->label(__('Monthly Installment'))
                            ->money('USD'),
                        TextEntry::make('installments')
                            ->label(__('Total Installments')),
                        TextEntry::make('paid_installments')
                            ->label(__('Paid Installments')),
                        TextEntry::make('remaining_amount')
                            ->label(__('Remaining Balance'))
                            ->money('USD'),
                        TextEntry::make('paid_percentage')
                            ->label(__('Progress'))
                            ->suffix('%'),
                    ])->columns(4),
                Section::make(__('Dates'))
                    ->schema([
                        TextEntry::make('start_date')
                            ->label(__('Start Date'))
                            ->date(),
                        TextEntry::make('end_date')
                            ->label(__('End Date'))
                            ->date(),
                        TextEntry::make('approved_at')
                            ->label(__('Approved At'))
                            ->dateTime(),
                        TextEntry::make('approver.name')
                            ->label(__('Approved By')),
                    ])->columns(4),
                Section::make(__('Notes'))
                    ->schema([
                        TextEntry::make('reason')
                            ->label(__('Reason')),
                        TextEntry::make('notes')
                            ->label(__('Notes')),
                    ])->columns(2),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getLabel(): string
    {
        return __('Employee Loan');
    }

    public static function getPluralLabel(): string
    {
        return __('Employee Loans');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployeeLoans::route('/'),
            'create' => CreateEmployeeLoan::route('/create'),
            'view' => ViewEmployeeLoan::route('/{record}'),
        ];
    }
}
