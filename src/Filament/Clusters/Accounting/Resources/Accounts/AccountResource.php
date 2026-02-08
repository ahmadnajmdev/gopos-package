<?php

namespace Gopos\Filament\Clusters\Accounting\Resources\Accounts;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Gopos\Filament\Clusters\Accounting\AccountingCluster;
use Gopos\Filament\Clusters\Accounting\Resources\Accounts\Pages\CreateAccount;
use Gopos\Filament\Clusters\Accounting\Resources\Accounts\Pages\EditAccount;
use Gopos\Filament\Clusters\Accounting\Resources\Accounts\Pages\ListAccounts;
use Gopos\Models\Account;
use Gopos\Models\AccountType;
use Gopos\Models\Currency;

class AccountResource extends Resource
{
    protected static ?string $cluster = AccountingCluster::class;

    protected static ?string $model = Account::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?int $navigationSort = 21;

    public static function getNavigationGroup(): ?string
    {
        return __('Accounting');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('account_type_id')
                    ->label(__('Account Type'))
                    ->options(fn () => AccountType::orderBy('display_order')->get()->pluck('localized_name', 'id'))
                    ->required()
                    ->searchable(),
                Select::make('parent_id')
                    ->label(__('Parent Account'))
                    ->options(fn () => Account::query()
                        ->whereNull('parent_id')
                        ->orderBy('code')
                        ->get()
                        ->pluck('display_name', 'id'))
                    ->searchable()
                    ->nullable(),
                TextInput::make('code')
                    ->label(__('Account Code'))
                    ->required()
                    ->maxLength(20)
                    ->unique(ignoreRecord: true),
                TextInput::make('name')
                    ->label(__('Name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('name_ar')
                    ->label(__('Name (Arabic)'))
                    ->maxLength(255),
                TextInput::make('name_ckb')
                    ->label(__('Name (Kurdish)'))
                    ->maxLength(255),
                TextInput::make('opening_balance')
                    ->label(__('Opening Balance'))
                    ->numeric()
                    ->default(0),
                Checkbox::make('is_active')
                    ->label(__('Active'))
                    ->default(true),
                Textarea::make('description')
                    ->label(__('Description'))
                    ->rows(2),
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
                TextColumn::make('localized_name')
                    ->label(__('Name'))
                    ->searchable(['name', 'name_ar', 'name_ckb'])
                    ->sortable('name'),
                TextColumn::make('accountType.localized_name')
                    ->label(__('Type'))
                    ->badge()
                    ->color(fn (Account $record) => match ($record->accountType?->name) {
                        'Asset' => 'success',
                        'Liability' => 'danger',
                        'Equity' => 'info',
                        'Revenue' => 'primary',
                        'Expense' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('parent.localized_name')
                    ->label(__('Parent'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('current_balance')
                    ->label(__('Balance'))
                    ->numeric(locale: 'en', decimalPlaces: 2)
                    ->suffix(' '.(Currency::getBaseCurrency()?->symbol ?? ''))
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),
                IconColumn::make('is_system')
                    ->label(__('System'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('code')
            ->filters([
                SelectFilter::make('account_type_id')
                    ->label(__('Account Type'))
                    ->options(fn () => AccountType::orderBy('display_order')->get()->pluck('localized_name', 'id')),
                SelectFilter::make('is_active')
                    ->label(__('Status'))
                    ->options([
                        '1' => __('Active'),
                        '0' => __('Inactive'),
                    ]),
            ])
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (Account $record) {
                        if (! $record->canBeDeleted()) {
                            throw new \Exception(__('This account cannot be deleted.'));
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getLabel(): string
    {
        return __('Account');
    }

    public static function getPluralLabel(): string
    {
        return __('Chart of Accounts');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAccounts::route('/'),
            'create' => CreateAccount::route('/create'),
            'edit' => EditAccount::route('/{record}/edit'),
        ];
    }
}
