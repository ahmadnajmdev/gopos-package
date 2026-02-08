<?php

namespace Gopos\Filament\Clusters\Accounting\Resources\Currencies\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CurrencyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('symbol'),
                TextEntry::make('code'),
                TextEntry::make('exchange_rate')
                    ->formatStateUsing(fn ($state) => rtrim(rtrim(number_format($state, 12), '0'), '.')),
                TextEntry::make('decimal_places')
                    ->numeric(),
                IconEntry::make('base')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
