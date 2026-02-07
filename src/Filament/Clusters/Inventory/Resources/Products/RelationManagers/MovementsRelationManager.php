<?php

namespace Gopos\Filament\Clusters\Inventory\Resources\Products\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'movements';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')->badge()->label(__('Type')),
                TextColumn::make('quantity')->numeric(locale: 'en')->label(__('Quantity')),
                TextColumn::make('reason')->limit(40)->label(__('Reason')),
                TextColumn::make('movement_date')->dateTime()->label(__('Date')),
                TextColumn::make('created_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([])
            ->recordActions([])
            ->bulkActions([]);
    }
}
