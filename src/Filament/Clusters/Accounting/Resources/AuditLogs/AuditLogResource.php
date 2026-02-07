<?php

namespace Gopos\Filament\Clusters\Accounting\Resources\AuditLogs;

use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Gopos\Filament\Clusters\Accounting\AccountingCluster;
use Gopos\Filament\Clusters\Accounting\Resources\AuditLogs\Pages\ListAuditLogs;
use Gopos\Filament\Clusters\Accounting\Resources\AuditLogs\Pages\ViewAuditLog;
use Gopos\Models\AuditLog;

class AuditLogResource extends Resource
{
    protected static ?string $cluster = AccountingCluster::class;

    protected static ?string $model = AuditLog::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-magnifying-glass';

    protected static ?int $navigationSort = 25;

    public static function getNavigationGroup(): ?string
    {
        return __('Accounting');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('Date/Time'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('user_name')
                    ->label(__('User'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('event')
                    ->label(__('Event'))
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        'restored' => 'info',
                        'voided' => 'danger',
                        'posted' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'created' => __('Created'),
                        'updated' => __('Updated'),
                        'deleted' => __('Deleted'),
                        'restored' => __('Restored'),
                        'voided' => __('Voided'),
                        'posted' => __('Posted'),
                        default => ucfirst($state),
                    }),
                TextColumn::make('model_name')
                    ->label(__('Model'))
                    ->searchable(query: fn ($query, $search) => $query->where('auditable_type', 'like', "%{$search}%")),
                TextColumn::make('auditable_id')
                    ->label(__('Record ID')),
                TextColumn::make('ip_address')
                    ->label(__('IP Address'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('event')
                    ->label(__('Event'))
                    ->options([
                        'created' => __('Created'),
                        'updated' => __('Updated'),
                        'deleted' => __('Deleted'),
                        'restored' => __('Restored'),
                        'voided' => __('Voided'),
                        'posted' => __('Posted'),
                    ]),
                SelectFilter::make('auditable_type')
                    ->label(__('Model'))
                    ->options([
                        'Gopos\\Models\\Sale' => __('Sale'),
                        'Gopos\\Models\\Purchase' => __('Purchase'),
                        'Gopos\\Models\\Expense' => __('Expense'),
                        'Gopos\\Models\\Income' => __('Income'),
                        'Gopos\\Models\\Payment' => __('Payment'),
                        'Gopos\\Models\\JournalEntry' => __('Journal Entry'),
                        'Gopos\\Models\\Account' => __('Account'),
                        'Gopos\\Models\\TaxCode' => __('Tax Code'),
                    ]),
            ])
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function getLabel(): string
    {
        return __('Audit Log');
    }

    public static function getPluralLabel(): string
    {
        return __('Audit Trail');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAuditLogs::route('/'),
            'view' => ViewAuditLog::route('/{record}'),
        ];
    }
}
