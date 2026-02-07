<?php

namespace Gopos\Filament\Clusters\Accounting\Resources\Incomes;

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
use Gopos\Filament\Clusters\Accounting\Resources\Incomes\Pages\ManageIncomes;
use Gopos\Filament\Clusters\Accounting\Resources\IncomeTypes\IncomeTypeResource;
use Gopos\Models\Currency;
use Gopos\Models\Income;
use Illuminate\Database\Eloquent\Builder;

class IncomeResource extends Resource
{
    protected static ?string $cluster = AccountingCluster::class;

    protected static ?string $model = Income::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-trending-up';

    protected static ?int $navigationSort = 9;

    public static function getNavigationGroup(): ?string
    {
        return __('Accounting');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Income Details'))
                    ->schema([
                        DatePicker::make('income_date')
                            ->label(__('Date'))
                            ->default(now())
                            ->required(),
                        Select::make('income_type_id')
                            ->label(__('Income Type'))
                            ->relationship('type', 'name')
                            ->required()
                            ->createOptionForm(fn (Schema $schema) => IncomeTypeResource::form($schema)),
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
                            ->numeric()
                            ->suffix(function (Get $get) {
                                $currency = Currency::find($get('currency_id'));

                                return $currency ? $currency->symbol : Currency::getBaseCurrency()?->symbol;
                            }),
                        Textarea::make('description')
                            ->label(__('Description'))
                            ->columnSpanFull(),
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
                Section::make(__('Income Information'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('type.name')
                                    ->label(__('Income Type'))
                                    ->badge()
                                    ->color('success')
                                    ->icon('heroicon-o-arrow-trending-up'),
                                TextEntry::make('income_date')
                                    ->label(__('Date'))
                                    ->date()
                                    ->icon('heroicon-o-calendar'),
                                TextEntry::make('amount')
                                    ->label(__('Amount'))
                                    ->numeric(locale: 'en')
                                    ->suffix(fn ($record) => ' '.($record->currency?->symbol ?? $currencySymbol))
                                    ->weight(FontWeight::Bold)
                                    ->color('success'),
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
                                TextEntry::make('description')
                                    ->label(__('Description'))
                                    ->placeholder(__('No description'))
                                    ->columnSpanFull(),
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
                TextColumn::make('income_date')
                    ->label(__('Date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('type.name')
                    ->label(__('Type'))
                    ->badge()
                    ->color('success')
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
                TextColumn::make('description')
                    ->label(__('Description'))
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('Updated'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('income_date', 'desc')
            ->filters([
                SelectFilter::make('income_type_id')
                    ->label(__('Income Type'))
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
                    ->query(fn (Builder $query): Builder => $query->whereDate('income_date', today()))
                    ->toggle(),
                QueryBuilder::make()
                    ->constraints([
                        RelationshipConstraint::make('type')
                            ->label(__('Income Type'))
                            ->relationship('type', 'name'),
                        RelationshipConstraint::make('currency')
                            ->label(__('Currency'))
                            ->relationship('currency', 'name'),
                        NumberConstraint::make('amount')
                            ->label(__('Amount')),
                        TextConstraint::make('description')
                            ->label(__('Description'))
                            ->nullable(),
                        TextConstraint::make('note')
                            ->label(__('Note'))
                            ->nullable(),
                        DateConstraint::make('income_date')
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
        return __('Income');
    }

    public static function getPluralLabel(): string
    {
        return __('Incomes');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageIncomes::route('/'),
        ];
    }
}
