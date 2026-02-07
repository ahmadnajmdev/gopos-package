<?php

namespace Gopos\Filament\Widgets;

use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Gopos\Models\Currency;
use Gopos\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class BestSellingProductsWidget extends TableWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 4;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Product::query()
                ->withCount('saleItems')
                ->orderBy('sale_items_count', 'desc')
                ->limit(5)
            )
            ->heading(__('Best Selling Products'))
            ->columns([
                ImageColumn::make('image')->square(),
                TextColumn::make('name'),
                TextColumn::make('category.name')
                    ->numeric(locale: 'en')
                    ->sortable(),
                TextColumn::make('barcode')
                    ->label(__('Barcode'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('price')
                    ->numeric(locale: 'en')
                    ->suffix(' '.(Currency::getBaseCurrency()?->symbol ?? ''))
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')->relationship('category', 'name'),

            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
