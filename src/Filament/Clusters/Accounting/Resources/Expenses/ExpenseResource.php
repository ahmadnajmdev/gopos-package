<?php

namespace Gopos\Filament\Clusters\Accounting\Resources\Expenses;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Gopos\Filament\Clusters\Accounting\AccountingCluster;
use Gopos\Filament\Clusters\Accounting\Resources\Expenses\Pages\ManageExpenses;
use Gopos\Filament\Clusters\Accounting\Resources\ExpenseTypes\ExpenseTypeResource;
use Gopos\Models\Currency;
use Gopos\Models\Expense;
use Illuminate\Database\Eloquent\Builder;

class ExpenseResource extends Resource
{
    protected static ?string $cluster = AccountingCluster::class;

    protected static ?string $model = Expense::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-trending-down';

    protected static ?int $navigationSort = 11;

    public static function getNavigationGroup(): ?string
    {
        return __('Accounting');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Expense Details'))
                    ->schema([
                        DatePicker::make('expense_date')
                            ->label(__('Date'))
                            ->default(now())
                            ->required(),
                        Select::make('expense_type_id')
                            ->label(__('Expense Type'))
                            ->relationship('type', 'name')
                            ->createOptionForm(fn (Schema $schema) => ExpenseTypeResource::form($schema))
                            ->required(),
                        Select::make('currency_id')
                            ->label(__('Currency'))
                            ->relationship('currency', 'name')
                            ->live()
                            ->default(fn (callable $get) => Currency::getBaseCurrency()?->id)
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state) {
                                $oldCurrency = Currency::find($old);
                                $currency = Currency::find($state);
                                $oldAmount = $get('amount');
                                if ($oldCurrency) {
                                    $newAmount = $oldAmount ? Currency::convertToCurrency($oldAmount, $currency?->code, $oldCurrency->code) : null;
                                } else {
                                    $newAmount = $oldAmount ? Currency::convertToCurrency($oldAmount, $currency?->code, Currency::getBaseCurrency()?->code) : null;
                                }
                                $set('amount', $newAmount);
                                $set('exchange_rate', $currency ? $currency->exchange_rate : (Currency::getBaseCurrency()?->exchange_rate ?? 1));
                            })
                            ->required(),
                        Hidden::make('exchange_rate')
                            ->default(fn (callable $get) => $get('currency')?->exchange_rate ?? Currency::getBaseCurrency()?->exchange_rate ?? 1),
                        TextInput::make('amount')
                            ->label(__('Amount'))
                            ->required()
                            ->suffix(function (Get $get) {
                                $currency = Currency::find($get('currency_id'));

                                return $currency ? $currency->symbol : Currency::getBaseCurrency()?->symbol;
                            })
                            ->numeric(),
                        Textarea::make('note')
                            ->label(__('Note'))
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        $currencySymbol = Currency::getBaseCurrency()?->symbol ?? '$';

        return $schema
            ->components([
                Section::make(__('Expense Information'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('type.name')
                                    ->label(__('Expense Type'))
                                    ->badge()
                                    ->color('danger')
                                    ->icon('heroicon-o-arrow-trending-down'),
                                TextEntry::make('expense_date')
                                    ->label(__('Date'))
                                    ->date()
                                    ->icon('heroicon-o-calendar'),
                                TextEntry::make('amount')
                                    ->label(__('Amount'))
                                    ->numeric(locale: 'en')
                                    ->suffix(fn ($record) => ' '.($record->currency?->symbol ?? $currencySymbol))
                                    ->weight(FontWeight::Bold)
                                    ->color('danger'),
                            ]),
                    ]),
                Section::make(__('Currency Details'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('currency.name')
                                    ->label(__('Currency'))
                                    ->icon('heroicon-o-currency-dollar'),
                                TextEntry::make('exchange_rate')
                                    ->label(__('Exchange Rate'))
                                    ->numeric(locale: 'en'),
                                TextEntry::make('amount_in_base_currency')
                                    ->label(__('Amount in Base Currency'))
                                    ->getStateUsing(fn ($record) => $record->currency?->convertFromCurrency($record->amount, $record->currency->code) ?? $record->amount)
                                    ->numeric(locale: 'en')
                                    ->suffix(fn () => ' '.$currencySymbol),
                            ]),
                    ])
                    ->collapsible(),
                Section::make(__('Additional Information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('note')
                                    ->label(__('Note'))
                                    ->placeholder(__('No notes'))
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
                TextColumn::make('expense_date')
                    ->label(__('Date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('type.name')
                    ->label(__('Type'))
                    ->badge()
                    ->color('danger')
                    ->sortable(),
                TextColumn::make('amount')
                    ->label(__('Amount'))
                    ->numeric(locale: 'en')
                    ->suffix(fn ($record) => ' '.($record->currency?->symbol ?? ''))
                    ->sortable(),
                TextColumn::make('currency.code')
                    ->label(__('Currency'))
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('note')
                    ->label(__('Note'))
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('Updated'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('expense_date', 'desc')
            ->filters([
                SelectFilter::make('expense_type_id')
                    ->label(__('Expense Type'))
                    ->relationship('type', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('currency_id')
                    ->label(__('Currency'))
                    ->relationship('currency', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('today')
                    ->label(__('Today'))
                    ->query(fn (Builder $query): Builder => $query->whereDate('expense_date', today()))
                    ->toggle(),
                QueryBuilder::make()
                    ->constraints([
                        RelationshipConstraint::make('type')
                            ->label(__('Expense Type'))
                            ->relationship('type', 'name'),
                        RelationshipConstraint::make('currency')
                            ->label(__('Currency'))
                            ->relationship('currency', 'name'),
                        NumberConstraint::make('amount')
                            ->label(__('Amount')),
                        TextConstraint::make('note')
                            ->label(__('Note'))
                            ->nullable(),
                        DateConstraint::make('expense_date')
                            ->label(__('Date')),
                    ]),
            ])
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
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

    public static function getLabel(): string
    {
        return __('Expense');
    }

    public static function getPluralLabel(): string
    {
        return __('Expenses');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageExpenses::route('/'),
        ];
    }
}
