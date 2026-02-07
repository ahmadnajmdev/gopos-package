<?php

namespace Gopos\Filament\Clusters\Accounting\Resources\JournalEntries\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;
use Gopos\Filament\Clusters\Accounting\Resources\JournalEntries\JournalEntryResource;

class ViewJournalEntry extends ViewRecord
{
    protected static string $resource = JournalEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn () => $this->record->canBeEdited()),
            Action::make('post')
                ->label(__('Post'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {
                    if (! $this->record->post()) {
                        throw new \Exception(__('Failed to post. Ensure debits equal credits.'));
                    }
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                })
                ->visible(fn () => $this->record->status === 'draft'),
            Action::make('void')
                ->label(__('Void'))
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->form([
                    Textarea::make('reason')
                        ->label(__('Void Reason'))
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->void($data['reason']);
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                })
                ->visible(fn () => $this->record->canBeVoided()),
        ];
    }
}
