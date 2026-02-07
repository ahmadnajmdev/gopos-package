<?php

namespace Gopos\Filament\Clusters\Inventory\Resources\StockTransfers\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Gopos\Filament\Clusters\Inventory\Resources\StockTransfers\StockTransferResource;
use Gopos\Services\InventoryService;

class EditStockTransfer extends EditRecord
{
    protected static string $resource = StockTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
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
                    // Update quantities sent to match requested
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

            Action::make('cancel')
                ->label(__('Cancel'))
                ->color('danger')
                ->icon('heroicon-o-x-mark')
                ->visible(fn () => in_array($this->record->status, ['draft', 'pending']))
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['status' => 'cancelled']);
                    Notification::make()
                        ->warning()
                        ->title(__('Transfer cancelled'))
                        ->send();
                }),

            DeleteAction::make()
                ->visible(fn () => $this->record->status === 'draft'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
