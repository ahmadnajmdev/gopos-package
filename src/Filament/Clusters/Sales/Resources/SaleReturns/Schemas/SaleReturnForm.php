<?php

namespace Gopos\Filament\Clusters\Sales\Resources\SaleReturns\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Gopos\Models\Currency;
use Gopos\Models\Product;
use Gopos\Models\Sale;
use Gopos\Models\SaleItem;
use Gopos\Models\SaleReturn;
use Gopos\Models\SaleReturnItem;
use Illuminate\Contracts\Database\Query\Builder;

class SaleReturnForm
{
    /**
     * Round monetary values to 2 decimal places
     */
    private static function roundMoney(float $amount): float
    {
        return round($amount, 2);
    }

    /**
     * Calculate individual item total
     */
    private static function calculateItemTotal(Set $set, Get $get): void
    {
        $price = (float) ($get('price') ?? 0);
        $qty = (float) ($get('return_stock') ?? 0);
        $itemTotal = self::roundMoney($price * $qty);

        $set('return_total_amount', $itemTotal);

        // Trigger global recalculation
        self::recalculateGlobalTotals($set, $get);
    }

    /**
     * Recalculate all global totals (subtotal, discount, total, paid)
     */
    private static function recalculateGlobalTotals(Set $set, Get $get): void
    {
        // Determine if we're inside a repeater item (2 levels up) or at root level
        $contextPrefix = $get('../../sale_id') !== null ? '../../' : '';

        $items = $get($contextPrefix.'items') ?? [];
        $subTotal = 0.0;

        foreach ($items as $item) {
            $subTotal += (float) ($item['return_total_amount'] ?? 0);
        }

        $subTotal = self::roundMoney($subTotal);
        $set($contextPrefix.'sub_total', $subTotal);

        $discount = (float) ($get($contextPrefix.'discount') ?? 0);
        $total = max(0, self::roundMoney($subTotal - $discount));

        $set($contextPrefix.'total_amount', $total);
        $set($contextPrefix.'paid_amount', $total);
    }

    /**
     * Calculate total when only discount changes
     */
    private static function recalculateTotal(Set $set, Get $get): void
    {
        $subTotal = (float) ($get('sub_total') ?? 0);
        $discount = (float) ($get('discount') ?? 0);
        $total = max(0, self::roundMoney($subTotal - $discount));

        $set('total_amount', $total);
        $set('paid_amount', $total);
    }

    /**
     * Get maximum returnable quantity for a product
     */
    private static function getMaxReturnableQuantity(int $saleId, int $productId): float
    {
        // Get the original sold quantity
        $soldQty = (float) (SaleItem::query()
            ->where('sale_id', $saleId)
            ->where('product_id', $productId)
            ->value('stock') ?? 0);

        // Get already returned quantity (only completed returns)
        $returnedQty = (float) (SaleReturnItem::query()
            ->where('product_id', $productId)
            ->whereHas('saleReturn', function ($query) use ($saleId) {
                $query->where('sale_id', $saleId);
            })
            ->sum('return_stock'));

        return max(0, $soldQty - $returnedQty);
    }

    /**
     * Load sale data and pre-fill form
     */
    private static function loadSaleData(Set $set, Get $get, $saleId): void
    {
        if (empty($saleId)) {
            // Reset form if no sale selected
            $set('currency_id', null);
            $set('sale_return_date', now());
            $set('items', []);
            $set('sub_total', 0);
            $set('discount', 0);
            $set('total_amount', 0);
            $set('paid_amount', 0);

            return;
        }

        $sale = Sale::with(['currency', 'items.product'])->find($saleId);

        if (! $sale) {
            return;
        }

        // Set currency and date
        $set('currency_id', $sale->currency_id);
        $set('sale_return_date', now());

        // Pre-fill items from sale
        $items = [];
        foreach ($sale->items as $saleItem) {
            $maxReturnable = self::getMaxReturnableQuantity($saleId, $saleItem->product_id);

            // Only add items that can still be returned
            if ($maxReturnable > 0) {
                $items[] = [
                    'product_id' => $saleItem->product_id,
                    'price' => $saleItem->price,
                    'return_stock' => $maxReturnable,
                    'return_total_amount' => self::roundMoney($saleItem->price * $maxReturnable),
                ];
            }
        }

        $set('items', $items);

        // Calculate initial totals
        self::recalculateGlobalTotals($set, $get);
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Return Details'))
                    ->schema([
                        Select::make('sale_id')
                            ->label(__('Sale'))
                            ->relationship(
                                'sale',
                                'sale_number',
                                modifyQueryUsing: fn (Builder $query) => $query
                                    ->whereDoesntHave('returns')
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                self::loadSaleData($set, $get, $state);
                            })
                            ->required(),

                        DatePicker::make('sale_return_date')
                            ->label(__('Return Date'))
                            ->default(now())
                            ->maxDate(now())
                            ->required(),

                        Select::make('currency_id')
                            ->label(__('Currency'))
                            ->relationship('currency', 'symbol')
                            ->disabled()
                            ->dehydrated()
                            ->required(),

                        TextInput::make('sale_return_number')
                            ->label(__('Return Number'))
                            ->default(fn () => SaleReturn::generateSaleReturnNumber())
                            ->readOnly()
                            ->dehydrated()
                            ->required(),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),

                Section::make(__('Items'))
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->live(onBlur: true)
                            ->minItems(1)
                            ->defaultItems(0)
                            ->addActionLabel(__('Add Item'))
                            ->table([
                                TableColumn::make(__('Product')),
                                TableColumn::make(__('Quantity')),
                                TableColumn::make(__('Price')),
                                TableColumn::make(__('Total')),
                            ])
                            ->schema([
                                Select::make('product_id')
                                    ->label(__('Product'))
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->disableOptionWhen(function (string $value, Get $get) {
                                        $saleId = $get('../../sale_id');
                                        if (! $saleId) {
                                            return true;
                                        }
                                        // Get all product_ids from the sale invoice
                                        $saleItemProductIds = SaleItem::query()
                                            ->where('sale_id', $saleId)
                                            ->pluck('product_id')
                                            ->toArray();

                                        // Get all selected product_ids in the current repeater (including current row)
                                        $items = $get('../../items') ?? [];
                                        $productIdCounts = [];
                                        foreach ($items as $item) {
                                            if (isset($item['product_id'])) {
                                                $pid = $item['product_id'];
                                                if (! isset($productIdCounts[$pid])) {
                                                    $productIdCounts[$pid] = 0;
                                                }
                                                $productIdCounts[$pid]++;
                                            }
                                        }

                                        // Disable if this product_id is not in the sale invoice
                                        $isNotInSale = ! in_array($value, $saleItemProductIds);

                                        // Disable if this product_id appears more than once in the items array
                                        $isDuplicate = isset($productIdCounts[$value]) && $productIdCounts[$value] > 1;

                                        return $isNotInSale || $isDuplicate;
                                    })
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                        if (empty($state)) {
                                            return;
                                        }

                                        $saleId = $get('../../sale_id');
                                        if (! $saleId) {
                                            return;
                                        }

                                        // Get the price from the original sale
                                        $saleItem = SaleItem::query()
                                            ->where('sale_id', $saleId)
                                            ->where('product_id', $state)
                                            ->first();

                                        if ($saleItem) {
                                            $set('price', $saleItem->price);
                                            $maxQty = self::getMaxReturnableQuantity($saleId, $state);
                                            $set('return_stock', min(1, $maxQty));
                                            self::calculateItemTotal($set, $get);
                                        }
                                    }),

                                TextInput::make('return_stock')
                                    ->label(__('Quantity'))
                                    ->numeric()
                                    ->required()
                                    ->minValue(0.01)
                                    ->live(debounce: 300)
                                    ->maxValue(function (Get $get) {
                                        $saleId = $get('../../sale_id');
                                        $saleItem = SaleItem::query()->where('sale_id', $saleId)->where('product_id', $get('product_id'))->first();

                                        return $saleItem?->stock ?? 0;
                                    })
                                    ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateItemTotal($set, $get))
                                    ->suffix(function (Get $get) {
                                        $productId = $get('product_id');
                                        if (! $productId) {
                                            return '';
                                        }
                                        $product = Product::query()->find($productId);

                                        return $product?->unit?->abbreviation ?? '';
                                    }),

                                TextInput::make('price')
                                    ->label(__('Price'))
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->live(debounce: 300)
                                    ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateItemTotal($set, $get))
                                    ->prefix(function (Get $get) {
                                        $currencyId = $get('../../currency_id');
                                        if (! $currencyId) {
                                            return '';
                                        }
                                        $currency = Currency::find($currencyId);

                                        return $currency?->symbol ?? '';
                                    }),

                                TextInput::make('return_total_amount')
                                    ->label(__('Total'))
                                    ->numeric()
                                    ->readOnly()
                                    ->dehydrated()
                                    ->required()
                                    ->prefix(function (Get $get) {
                                        $currencyId = $get('../../currency_id');
                                        if (! $currencyId) {
                                            return '';
                                        }
                                        $currency = Currency::find($currencyId);

                                        return $currency?->symbol ?? '';
                                    }),
                            ])
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                self::recalculateGlobalTotals($set, $get);
                            })
                            ->deleteAction(
                                fn ($action) => $action->after(fn (Set $set, Get $get) => self::recalculateGlobalTotals($set, $get))
                            ),
                    ])
                    ->columnSpanFull(),

                Section::make(__('Totals'))
                    ->schema([
                        TextInput::make('sub_total')
                            ->label(__('Subtotal'))
                            ->readOnly()
                            ->numeric()
                            ->default(0.00)
                            ->dehydrated()
                            ->required()
                            ->prefix(function (Get $get) {
                                $currencyId = $get('currency_id');
                                if (! $currencyId) {
                                    return '';
                                }
                                $currency = Currency::find($currencyId);

                                return $currency?->symbol ?? '';
                            }),

                        TextInput::make('discount')
                            ->label(__('Discount'))
                            ->numeric()
                            ->default(0.00)
                            ->minValue(0)
                            ->maxValue(fn (Get $get) => $get('sub_total') ?? 0)
                            ->live(debounce: 300)
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::recalculateTotal($set, $get))
                            ->required()
                            ->prefix(function (Get $get) {
                                $currencyId = $get('currency_id');
                                if (! $currencyId) {
                                    return '';
                                }
                                $currency = Currency::find($currencyId);

                                return $currency?->symbol ?? '';
                            }),

                        TextInput::make('total_amount')
                            ->label(__('Total Amount'))
                            ->readOnly()
                            ->numeric()
                            ->dehydrated()
                            ->required()
                            ->prefix(function (Get $get) {
                                $currencyId = $get('currency_id');
                                if (! $currencyId) {
                                    return '';
                                }
                                $currency = Currency::find($currencyId);

                                return $currency?->symbol ?? '';
                            }),

                        TextInput::make('paid_amount')
                            ->label(__('Paid Amount'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(fn (Get $get) => $get('total_amount') ?? 0)
                            ->default(0.00)
                            ->required()
                            ->prefix(function (Get $get) {
                                $currencyId = $get('currency_id');
                                if (! $currencyId) {
                                    return '';
                                }
                                $currency = Currency::find($currencyId);

                                return $currency?->symbol ?? '';
                            }),

                    ])
                    ->columns(4)
                    ->columnSpanFull(),

                Section::make(__('Notes'))
                    ->schema([
                        Textarea::make('reason')
                            ->label(__('Reason for Return'))
                            ->rows(2),
                        Textarea::make('note')
                            ->label(__('Additional Notes'))
                            ->rows(2),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsible(),
            ]);
    }
}
