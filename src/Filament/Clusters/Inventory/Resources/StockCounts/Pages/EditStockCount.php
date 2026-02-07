<?php

namespace Gopos\Filament\Clusters\Inventory\Resources\StockCounts\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Gopos\Filament\Clusters\Inventory\Resources\StockCounts\StockCountResource;
use Gopos\Models\StockCountItem;
use Gopos\Services\InventoryService;

class EditStockCount extends EditRecord implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = StockCountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('start')
                ->label(__('Start Counting'))
                ->color('info')
                ->icon('heroicon-o-play')
                ->visible(fn () => $this->record->status === 'draft')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->start();
                    Notification::make()
                        ->success()
                        ->title(__('Stock count started'))
                        ->send();
                }),

            Action::make('complete')
                ->label(__('Complete'))
                ->color('success')
                ->icon('heroicon-o-check')
                ->visible(fn () => $this->record->status === 'in_progress')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->complete(auth()->user());
                    Notification::make()
                        ->success()
                        ->title(__('Stock count completed'))
                        ->send();
                }),

            Action::make('post_adjustments')
                ->label(__('Post Adjustments'))
                ->color('warning')
                ->icon('heroicon-o-arrow-up-tray')
                ->visible(fn () => $this->record->status === 'completed' && ! $this->record->adjustments_posted)
                ->requiresConfirmation()
                ->modalDescription(__('This will create inventory adjustments for all variances. This action cannot be undone.'))
                ->action(function () {
                    $inventoryService = app(InventoryService::class);
                    $inventoryService->postStockCountAdjustments($this->record);
                    Notification::make()
                        ->success()
                        ->title(__('Adjustments posted successfully'))
                        ->send();
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                }),

            DeleteAction::make()
                ->visible(fn () => $this->record->status === 'draft'),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(StockCountItem::query()->where('stock_count_id', $this->record->id))
            ->columns([
                TextColumn::make('product.name')
                    ->label(__('Product'))
                    ->searchable(),
                TextColumn::make('system_quantity')
                    ->label(__('System Qty'))
                    ->numeric(2),
                TextInputColumn::make('counted_quantity')
                    ->label(__('Counted Qty'))
                    ->type('number')
                    ->rules(['numeric', 'min:0'])
                    ->disabled(fn () => ! in_array($this->record->status, ['draft', 'in_progress']))
                    ->afterStateUpdated(function ($record, $state) {
                        $variance = $state - $record->system_quantity;
                        $record->update([
                            'counted_quantity' => $state,
                            'variance' => $variance,
                            'variance_value' => $variance * $record->unit_cost,
                            'status' => StockCountItem::STATUS_COUNTED,
                            'counted_by' => auth()->id(),
                        ]);
                    }),
                TextColumn::make('variance')
                    ->label(__('Variance'))
                    ->numeric(2)
                    ->color(fn ($state) => $state > 0 ? 'success' : ($state < 0 ? 'danger' : 'gray')),
                TextColumn::make('variance_value')
                    ->label(__('Variance Value'))
                    ->money('USD')
                    ->color(fn ($state) => $state > 0 ? 'success' : ($state < 0 ? 'danger' : 'gray')),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => __('Pending'),
                        'counted' => __('Counted'),
                        'verified' => __('Verified'),
                        'adjusted' => __('Adjusted'),
                        default => $state,
                    }),
            ])
            ->paginated([10, 25, 50, 100]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
