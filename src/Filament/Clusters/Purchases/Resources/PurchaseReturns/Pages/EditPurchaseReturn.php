<?php

namespace Gopos\Filament\Clusters\Purchases\Resources\PurchaseReturns\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Gopos\Filament\Clusters\Purchases\Resources\PurchaseReturns\PurchaseReturnResource;
use Gopos\Models\InventoryMovement;
use Gopos\Models\Payment;
use Gopos\Models\PurchaseItem;
use Gopos\Models\PurchaseReturnItem;

class EditPurchaseReturn extends EditRecord
{
    protected static string $resource = PurchaseReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            Action::make('complete')
                ->label(__('Complete'))
                ->color('success')
                ->visible(fn (): bool => $this->record->status === 'Pending')
                ->requiresConfirmation()
                ->action(function (): void {
                    $return = $this->record;
                    // Prevent over-return: ensure each line does not exceed purchased qty minus previous completed returns
                    foreach ($return->items as $item) {
                        $purchased = (float) (PurchaseItem::query()
                            ->where('purchase_id', $return->purchase_id)
                            ->where('product_id', $item->product_id)
                            ->value('stock') ?? 0);

                        $previousReturned = (float) (PurchaseReturnItem::query()
                            ->where('product_id', $item->product_id)
                            ->whereHas('purchaseReturn', function ($q) use ($return) {
                                $q->where('purchase_id', $return->purchase_id)->where('status', 'Completed');
                            })
                            ->sum('return_stock'));

                        $available = max(0, $purchased - $previousReturned);
                        if ($item->return_stock > $available) {
                            $this->addError('items', __('Return quantity exceeds available for some items.'));

                            return;
                        }
                    }
                    foreach ($return->items as $item) {
                        InventoryMovement::create([
                            'product_id' => $item->product_id,
                            'type' => 'purchase_return',
                            'quantity' => -(int) $item->return_stock,
                            'purchase_id' => $return->purchase_id,
                            'user_id' => auth()->id(),
                            'reason' => 'Purchase return completed',
                            'movement_date' => now(),
                        ]);
                    }
                    $return->update(['status' => 'Completed']);

                    Payment::create([
                        'reference_id' => $return->id,
                        'type' => 'return',
                        'amount' => $return->total_amount,
                        'currency_id' => $return->currency_id,
                        'exchange_rate' => $return->exchange_rate,
                        'amount_in_base_currency' => $return->amount_in_base_currency,
                        'note' => 'Auto supplier debit on purchase return completion',
                    ]);
                }),
            Action::make('reject')
                ->label(__('Reject'))
                ->color('danger')
                ->visible(fn (): bool => $this->record->status === 'Pending')
                ->requiresConfirmation()
                ->action(function (): void {
                    $this->record->update(['status' => 'Rejected']);
                }),
            DeleteAction::make(),
        ];
    }
}
