<?php

namespace Gopos\Filament\Clusters\Inventory\Resources\StockTransfers\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Gopos\Filament\Clusters\Inventory\Resources\StockTransfers\StockTransferResource;
use Gopos\Services\InventoryService;

class ViewStockTransfer extends ViewRecord
{
    protected static string $resource = StockTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn () => $this->record->canEdit()),

            Action::make('approve')
                ->label(__('Approve'))
                ->color('success')
                ->icon('heroicon-o-check')
                ->visible(fn () => $this->record->status === 'draft')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update([
                        'status' => 'pending',
                        'approved_by' => auth()->id(),
                    ]);
                    Notification::make()
                        ->success()
                        ->title(__('Transfer approved'))
                        ->send();
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                }),

            Action::make('send')
                ->label(__('Mark In Transit'))
                ->color('info')
                ->icon('heroicon-o-truck')
                ->visible(fn () => $this->record->status === 'pending')
                ->requiresConfirmation()
                ->action(function () {
                    foreach ($this->record->items as $item) {
                        $item->update(['quantity_sent' => $item->quantity_requested]);
                    }
                    $this->record->update(['status' => 'in_transit']);
                    Notification::make()
                        ->success()
                        ->title(__('Transfer marked as in transit'))
                        ->send();
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                }),

            Action::make('complete')
                ->label(__('Complete Transfer'))
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->visible(fn () => in_array($this->record->status, ['in_transit', 'partial']))
                ->requiresConfirmation()
                ->action(function () {
                    $inventoryService = app(InventoryService::class);
                    $inventoryService->transferStock($this->record);
                    Notification::make()
                        ->success()
                        ->title(__('Transfer completed successfully'))
                        ->send();
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                }),
        ];
    }
}
