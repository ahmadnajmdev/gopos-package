<?php

namespace Gopos\Filament\Clusters\Purchases\Resources\PurchaseReturns\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PurchaseReturnInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('purchase_return_number')
                    ->label(__('Return Number'))
                    ->copyable(),
                TextEntry::make('purchase.purchase_number')
                    ->label(__('Purchase Number'))
                    ->copyable(),
                TextEntry::make('purchase.supplier.name')
                    ->label(__('Supplier')),
                TextEntry::make('purchase_return_date')
                    ->label(__('Return Date'))
                    ->date(),
                TextEntry::make('currency.code')
                    ->label(__('Currency')),
                TextEntry::make('exchange_rate')
                    ->label(__('Exchange Rate'))
                    ->numeric(),
                TextEntry::make('sub_total')
                    ->label(__('Subtotal'))
                    ->numeric()
                    ->money(fn ($record) => $record->currency?->code),
                TextEntry::make('discount')
                    ->numeric()
                    ->money(fn ($record) => $record->currency?->code),
                TextEntry::make('total_amount')
                    ->label(__('Total'))
                    ->numeric()
                    ->money(fn ($record) => $record->currency?->code)
                    ->weight('bold'),
                TextEntry::make('amount_in_base_currency')
                    ->label(__('Amount in Base Currency'))
                    ->numeric()
                    ->money(fn ($record) => config('app.currency')),
                TextEntry::make('paid_amount')
                    ->label(__('Paid'))
                    ->numeric()
                    ->money(fn ($record) => $record->currency?->code),
                TextEntry::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pending' => 'warning',
                        'Completed' => 'success',
                        'Rejected' => 'danger',
                        default => 'gray',
                    }),
                TextEntry::make('reason')
                    ->label(__('Return Reason'))
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('note')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label(__('Updated At'))
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
