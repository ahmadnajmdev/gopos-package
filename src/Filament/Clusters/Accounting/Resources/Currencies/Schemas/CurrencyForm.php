<?php

namespace Gopos\Filament\Clusters\Accounting\Resources\Currencies\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CurrencyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        // Basic Information Section
                        Section::make(__('Basic Information'))
                            ->description(__('Enter the basic currency details'))
                            ->icon('heroicon-o-currency-dollar')
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('Currency Name'))
                                    ->placeholder('e.g., US Dollar, Euro, British Pound')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText(__('The full name of the currency')),

                                TextInput::make('code')
                                    ->label(__('Currency Code'))
                                    ->placeholder('e.g., USD, EUR, GBP')
                                    ->required()
                                    ->maxLength(3)
                                    ->helperText(__('ISO 4217 currency code (3 letters)'))
                                    ->unique(ignoreRecord: true)
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $set('code', strtoupper($state));
                                    }),

                                TextInput::make('symbol')
                                    ->label(__('Currency Symbol'))
                                    ->placeholder('e.g., $, €, £')
                                    ->required()
                                    ->maxLength(10)
                                    ->helperText(__('The symbol used to represent this currency')),
                            ])
                            ->columns(1),

                        // Exchange Settings Section
                        Section::make(__('Exchange Settings'))
                            ->description(__('Configure exchange rate and formatting'))
                            ->icon('heroicon-o-calculator')
                            ->schema([
                                TextInput::make('exchange_rate')
                                    ->label(__('Exchange Rate'))
                                    ->placeholder('1.000000000000')
                                    ->required()
                                    ->numeric()
                                    ->step(0.000000000001)
                                    ->minValue(0.000000000001)
                                    ->helperText(__('Rate relative to base currency (1.00 = base currency). Supports up to 12 decimal places.')),

                                Select::make('decimal_places')
                                    ->label(__('Decimal Places'))
                                    ->options([
                                        0 => __('0 (No decimals)'),
                                        1 => __('1 decimal place'),
                                        2 => __('2 decimal places'),
                                        3 => __('3 decimal places'),
                                        4 => __('4 decimal places'),
                                    ])
                                    ->default(2)
                                    ->required()
                                    ->helperText(__('Number of decimal places to display')),

                                Toggle::make('base')
                                    ->label(__('Base Currency'))
                                    ->helperText(__('Only one currency can be the base currency'))
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $set('exchange_rate', 1.0000);
                                        }
                                    }),
                            ])
                            ->columns(1),
                    ]),

                // Additional Information Section
                Section::make(__('Additional Information'))
                    ->description(__('Additional currency details and status'))
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label(__('Created At'))
                                    ->dateTime('M j, Y g:i A'),

                                TextEntry::make('updated_at')
                                    ->label(__('Last Updated'))
                                    ->dateTime('M j, Y g:i A'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->visible(fn ($record) => $record !== null),
            ]);
    }
}
