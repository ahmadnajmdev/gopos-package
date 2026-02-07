<?php

namespace Gopos\Filament\Clusters\HumanResources\Resources\PayrollPeriods;

use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Gopos\Events\PayrollApproved;
use Gopos\Filament\Clusters\HumanResources\HumanResourcesCluster;
use Gopos\Filament\Clusters\HumanResources\Resources\PayrollPeriods\Pages\CreatePayrollPeriod;
use Gopos\Filament\Clusters\HumanResources\Resources\PayrollPeriods\Pages\ListPayrollPeriods;
use Gopos\Filament\Clusters\HumanResources\Resources\PayrollPeriods\Pages\ViewPayrollPeriod;
use Gopos\Jobs\ProcessPayrollJob;
use Gopos\Models\PayrollPeriod;

class PayrollPeriodResource extends Resource
{
    protected static ?string $cluster = HumanResourcesCluster::class;

    protected static ?string $model = PayrollPeriod::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?int $navigationSort = 7;

    public static function getNavigationGroup(): ?string
    {
        return __('Human Resources');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Period Information'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Name'))
                            ->required()
                            ->maxLength(255),
                        Select::make('year')
                            ->label(__('Year'))
                            ->options(array_combine(
                                range(now()->year - 1, now()->year + 1),
                                range(now()->year - 1, now()->year + 1)
                            ))
                            ->required()
                            ->default(now()->year),
                        Select::make('month')
                            ->label(__('Month'))
                            ->options([
                                1 => __('January'), 2 => __('February'), 3 => __('March'),
                                4 => __('April'), 5 => __('May'), 6 => __('June'),
                                7 => __('July'), 8 => __('August'), 9 => __('September'),
                                10 => __('October'), 11 => __('November'), 12 => __('December'),
                            ])
                            ->required()
                            ->default(now()->month),
                        DatePicker::make('start_date')
                            ->label(__('Start Date'))
                            ->required(),
                        DatePicker::make('end_date')
                            ->label(__('End Date'))
                            ->required(),
                        DatePicker::make('payment_date')
                            ->label(__('Payment Date'))
                            ->required(),
                        Textarea::make('notes')
                            ->label(__('Notes'))
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Period'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('period_label')
                    ->label(__('Month/Year'))
                    ->sortable(['year', 'month']),
                TextColumn::make('total_employees')
                    ->label(__('Employees'))
                    ->sortable(),
                TextColumn::make('total_gross')
                    ->label(__('Total Gross'))
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('total_deductions')
                    ->label(__('Deductions'))
                    ->money('USD')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('total_net')
                    ->label(__('Net Payroll'))
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => __('Draft'),
                        'processing' => __('Processing'),
                        'processed' => __('Processed'),
                        'approved' => __('Approved'),
                        'paid' => __('Paid'),
                        'cancelled' => __('Cancelled'),
                        default => $state,
                    })
                    ->colors([
                        'gray' => 'draft',
                        'info' => 'processing',
                        'warning' => 'processed',
                        'success' => 'approved',
                        'primary' => 'paid',
                        'danger' => 'cancelled',
                    ]),
                TextColumn::make('payment_date')
                    ->label(__('Payment Date'))
                    ->date()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'draft' => __('Draft'),
                        'processing' => __('Processing'),
                        'processed' => __('Processed'),
                        'approved' => __('Approved'),
                        'paid' => __('Paid'),
                    ]),
                SelectFilter::make('year')
                    ->label(__('Year'))
                    ->options(array_combine(
                        range(now()->year - 2, now()->year),
                        range(now()->year - 2, now()->year)
                    )),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('process')
                    ->label(__('Process'))
                    ->icon('heroicon-o-play')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (PayrollPeriod $record) => $record->canBeProcessed())
                    ->action(function (PayrollPeriod $record) {
                        $record->startProcessing();
                        ProcessPayrollJob::dispatch($record, auth()->id());
                        Notification::make()
                            ->title(__('Payroll processing started'))
                            ->success()
                            ->send();
                    }),
                Action::make('approve')
                    ->label(__('Approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (PayrollPeriod $record) => $record->canBeApproved())
                    ->action(function (PayrollPeriod $record) {
                        $record->approve(auth()->id());
                        event(new PayrollApproved($record));
                        Notification::make()
                            ->title(__('Payroll approved'))
                            ->success()
                            ->send();
                    }),
                Action::make('pay')
                    ->label(__('Mark as Paid'))
                    ->icon('heroicon-o-banknotes')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->visible(fn (PayrollPeriod $record) => $record->canBePaid())
                    ->action(function (PayrollPeriod $record) {
                        $record->markPaid(auth()->id());
                        Notification::make()
                            ->title(__('Payroll marked as paid'))
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('year', 'desc');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Period Information'))
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('Name')),
                        TextEntry::make('period_label')
                            ->label(__('Period')),
                        TextEntry::make('start_date')
                            ->label(__('Start Date'))
                            ->date(),
                        TextEntry::make('end_date')
                            ->label(__('End Date'))
                            ->date(),
                        TextEntry::make('payment_date')
                            ->label(__('Payment Date'))
                            ->date(),
                        TextEntry::make('status')
                            ->label(__('Status'))
                            ->badge(),
                    ])->columns(3),
                Section::make(__('Totals'))
                    ->schema([
                        TextEntry::make('total_employees')
                            ->label(__('Total Employees')),
                        TextEntry::make('total_gross')
                            ->label(__('Total Gross'))
                            ->money('USD'),
                        TextEntry::make('total_deductions')
                            ->label(__('Total Deductions'))
                            ->money('USD'),
                        TextEntry::make('total_net')
                            ->label(__('Total Net'))
                            ->money('USD'),
                    ])->columns(4),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getLabel(): string
    {
        return __('Payroll Period');
    }

    public static function getPluralLabel(): string
    {
        return __('Payroll Periods');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayrollPeriods::route('/'),
            'create' => CreatePayrollPeriod::route('/create'),
            'view' => ViewPayrollPeriod::route('/{record}'),
        ];
    }
}
