<?php

namespace Gopos\Filament\Clusters\Accounting\Resources\JournalEntries;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Gopos\Filament\Clusters\Accounting\AccountingCluster;
use Gopos\Filament\Clusters\Accounting\Resources\JournalEntries\Pages\CreateJournalEntry;
use Gopos\Filament\Clusters\Accounting\Resources\JournalEntries\Pages\EditJournalEntry;
use Gopos\Filament\Clusters\Accounting\Resources\JournalEntries\Pages\ListJournalEntries;
use Gopos\Filament\Clusters\Accounting\Resources\JournalEntries\Pages\ViewJournalEntry;
use Gopos\Filament\Forms\Components\JournalBalanceSummary;
use Gopos\Models\Account;
use Gopos\Models\Currency;
use Gopos\Models\JournalEntry;

class JournalEntryResource extends Resource
{
    protected static ?string $cluster = AccountingCluster::class;

    protected static ?string $model = JournalEntry::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 22;

    public static function getNavigationGroup(): ?string
    {
        return __('Accounting');
    }

    public static function form(Schema $schema): Schema
    {
        $currency = Currency::getBaseCurrency();
        $currencySymbol = $currency?->symbol ?? '';

        return $schema
            ->components([
                Section::make(__('Entry Details'))
                    ->icon(Heroicon::DocumentText)
                    ->description(__('Basic information about the journal entry'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('entry_number')
                                    ->label(__('Entry Number'))
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->placeholder(__('Auto-generated'))
                                    ->prefixIcon(Heroicon::OutlinedHashtag)
                                    ->visibleOn('edit'),
                                DatePicker::make('entry_date')
                                    ->label(__('Entry Date'))
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d M Y')
                                    ->default(now())
                                    ->prefixIcon(Heroicon::OutlinedCalendar)
                                    ->closeOnDateSelection()
                                    ->extraInputAttributes(['tabindex' => 1]),
                            ]),
                        Textarea::make('description')
                            ->label(__('Description'))
                            ->required()
                            ->rows(2)
                            ->placeholder(__('Enter a description for this journal entry...'))
                            ->extraInputAttributes(['tabindex' => 2])
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->columnSpanFull(),

                Section::make(__('Journal Lines'))
                    ->icon(Heroicon::TableCells)
                    ->description(__('Add debit and credit entries. Each line must have either a debit or credit amount.'))
                    ->schema([
                        Repeater::make('lines')
                            ->hiddenLabel()
                            ->relationship()
                            ->schema([
                                Grid::make(12)
                                    ->schema([
                                        Select::make('account_id')
                                            ->label(__('Account'))
                                            ->options(fn () => Account::query()
                                                ->where('is_active', true)
                                                ->orderBy('code')
                                                ->get()
                                                ->mapWithKeys(fn ($account) => [
                                                    $account->id => $account->code.' - '.$account->localized_name.' ('.$currencySymbol.number_format($account->current_balance, 2).')',
                                                ]))
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->placeholder(__('Select account...'))
                                            ->columnSpan(4),
                                        TextInput::make('description')
                                            ->label(__('Line Description'))
                                            ->placeholder(__('Optional description...'))
                                            ->maxLength(255)
                                            ->columnSpan(4),
                                        TextInput::make('debit')
                                            ->label(__('Debit'))
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0)
                                            ->step(0.01)
                                            ->prefix($currencySymbol)
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                                if ((float) $state > 0) {
                                                    $set('credit', 0);
                                                }
                                            })
                                            ->disabled(fn (Get $get): bool => (float) ($get('credit') ?? 0) > 0)
                                            ->dehydrated()
                                            ->extraInputAttributes([
                                                'class' => 'text-right tabular-nums',
                                                'x-on:focus' => '$el.select()',
                                            ])
                                            ->suffixAction(
                                                Action::make('autoFillDebit')
                                                    ->icon(Heroicon::Calculator)
                                                    ->tooltip(__('Auto-fill remaining balance'))
                                                    ->action(function (Get $get, Set $set) {
                                                        $lines = $get('../../') ?? [];
                                                        $totalDebit = collect($lines)->sum(fn ($l) => (float) ($l['debit'] ?? 0));
                                                        $totalCredit = collect($lines)->sum(fn ($l) => (float) ($l['credit'] ?? 0));
                                                        $diff = $totalCredit - $totalDebit;
                                                        if ($diff > 0) {
                                                            $set('debit', number_format($diff, 2, '.', ''));
                                                            $set('credit', 0);
                                                        }
                                                    })
                                                    ->visible(fn (Get $get): bool => (float) ($get('debit') ?? 0) == 0 && (float) ($get('credit') ?? 0) == 0)
                                            )
                                            ->columnSpan(2),
                                        TextInput::make('credit')
                                            ->label(__('Credit'))
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0)
                                            ->step(0.01)
                                            ->prefix($currencySymbol)
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                                if ((float) $state > 0) {
                                                    $set('debit', 0);
                                                }
                                            })
                                            ->disabled(fn (Get $get): bool => (float) ($get('debit') ?? 0) > 0)
                                            ->dehydrated()
                                            ->extraInputAttributes([
                                                'class' => 'text-right tabular-nums',
                                                'x-on:focus' => '$el.select()',
                                            ])
                                            ->suffixAction(
                                                Action::make('autoFillCredit')
                                                    ->icon(Heroicon::Calculator)
                                                    ->tooltip(__('Auto-fill remaining balance'))
                                                    ->action(function (Get $get, Set $set) {
                                                        $lines = $get('../../') ?? [];
                                                        $totalDebit = collect($lines)->sum(fn ($l) => (float) ($l['debit'] ?? 0));
                                                        $totalCredit = collect($lines)->sum(fn ($l) => (float) ($l['credit'] ?? 0));
                                                        $diff = $totalDebit - $totalCredit;
                                                        if ($diff > 0) {
                                                            $set('credit', number_format($diff, 2, '.', ''));
                                                            $set('debit', 0);
                                                        }
                                                    })
                                                    ->visible(fn (Get $get): bool => (float) ($get('debit') ?? 0) == 0 && (float) ($get('credit') ?? 0) == 0)
                                            )
                                            ->columnSpan(2),
                                    ])
                                    ->extraAttributes(fn (Get $get): array => [
                                        'class' => match (true) {
                                            (float) ($get('debit') ?? 0) > 0 => 'bg-emerald-50 dark:bg-emerald-900/10 rounded-lg p-2 -m-1 transition-colors duration-200',
                                            (float) ($get('credit') ?? 0) > 0 => 'bg-blue-50 dark:bg-blue-900/10 rounded-lg p-2 -m-1 transition-colors duration-200',
                                            default => 'transition-colors duration-200',
                                        },
                                    ]),
                            ])
                            ->addActionLabel(__('Add Line'))
                            ->reorderable()
                            ->reorderableWithDragAndDrop()
                            ->cloneable()
                            ->defaultItems(2)
                            ->minItems(2)
                            ->itemLabel(fn (array $state): ?string => $state['account_id']
                                ? Account::find($state['account_id'])?->name
                                : null)
                            ->live(debounce: 500)
                            ->rules([
                                fn (): \Closure => function (string $attribute, $value, \Closure $fail) {
                                    if (! is_array($value)) {
                                        return;
                                    }

                                    $totalDebit = collect($value)->sum(fn ($line) => (float) ($line['debit'] ?? 0));
                                    $totalCredit = collect($value)->sum(fn ($line) => (float) ($line['credit'] ?? 0));

                                    if (abs($totalDebit - $totalCredit) >= 0.01) {
                                        $fail(__('Journal entry must be balanced. Total Debit (:debit) must equal Total Credit (:credit).', [
                                            'debit' => number_format($totalDebit, 2),
                                            'credit' => number_format($totalCredit, 2),
                                        ]));
                                    }
                                },
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                JournalBalanceSummary::make()
                    ->statePath('lines')
                    ->currencySymbol($currencySymbol)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        $currencySymbol = Currency::getBaseCurrency()?->symbol ?? '';

        return $table
            ->columns([
                TextColumn::make('entry_number')
                    ->label(__('Entry #'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage(__('Entry number copied')),
                TextColumn::make('entry_date')
                    ->label(__('Date'))
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('description')
                    ->label(__('Description'))
                    ->limit(50)
                    ->searchable()
                    ->wrap(),
                TextColumn::make('total_debit')
                    ->label(__('Debit'))
                    ->numeric(locale: 'en', decimalPlaces: 2)
                    ->prefix($currencySymbol.' ')
                    ->sortable()
                    ->alignEnd()
                    ->color('success'),
                TextColumn::make('total_credit')
                    ->label(__('Credit'))
                    ->numeric(locale: 'en', decimalPlaces: 2)
                    ->prefix($currencySymbol.' ')
                    ->sortable()
                    ->alignEnd()
                    ->color('info'),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'draft' => 'warning',
                        'posted' => 'success',
                        'voided' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'draft' => __('Draft'),
                        'posted' => __('Posted'),
                        'voided' => __('Voided'),
                        default => $state,
                    }),
                TextColumn::make('createdByUser.name')
                    ->label(__('Created By'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('entry_date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'draft' => __('Draft'),
                        'posted' => __('Posted'),
                        'voided' => __('Voided'),
                    ]),
            ])
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                ViewAction::make(),
                Action::make('post')
                    ->label(__('Post'))
                    ->icon(Heroicon::CheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(__('Post Journal Entry'))
                    ->modalDescription(__('Are you sure you want to post this journal entry? This action cannot be undone.'))
                    ->modalSubmitActionLabel(__('Yes, Post Entry'))
                    ->action(function (JournalEntry $record) {
                        if (! $record->post()) {
                            throw new \Exception(__('Failed to post journal entry. Ensure debits equal credits.'));
                        }
                    })
                    ->visible(fn (JournalEntry $record) => $record->status === 'draft'),
                Action::make('void')
                    ->label(__('Void'))
                    ->icon(Heroicon::XCircle)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(__('Void Journal Entry'))
                    ->modalDescription(__('Are you sure you want to void this journal entry? This will reverse all account balance changes.'))
                    ->form([
                        Textarea::make('reason')
                            ->label(__('Void Reason'))
                            ->required()
                            ->placeholder(__('Please provide a reason for voiding this entry...')),
                    ])
                    ->action(function (JournalEntry $record, array $data) {
                        $record->void($data['reason']);
                    })
                    ->visible(fn (JournalEntry $record) => $record->canBeVoided()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if (! $record->canBeDeleted()) {
                                    throw new \Exception(__('Some entries cannot be deleted.'));
                                }
                            }
                        }),
                ]),
            ]);
    }

    public static function getLabel(): string
    {
        return __('Journal Entry');
    }

    public static function getPluralLabel(): string
    {
        return __('Journal Entries');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListJournalEntries::route('/'),
            'create' => CreateJournalEntry::route('/create'),
            'view' => ViewJournalEntry::route('/{record}'),
            'edit' => EditJournalEntry::route('/{record}/edit'),
        ];
    }
}
