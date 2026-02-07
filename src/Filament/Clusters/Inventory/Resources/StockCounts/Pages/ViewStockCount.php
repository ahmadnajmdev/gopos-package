<?php

namespace Gopos\Filament\Clusters\Inventory\Resources\StockCounts\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Gopos\Filament\Clusters\Inventory\Resources\StockCounts\StockCountResource;
use Gopos\Services\InventoryService;

class ViewStockCount extends ViewRecord
{
    protected static string $resource = StockCountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn () => in_array($this->record->status, ['draft', 'in_progress'])),

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
        ];
    }
}
