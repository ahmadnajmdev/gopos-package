<?php

namespace Gopos\Filament\Clusters\Accounting\Resources\JournalEntries\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Gopos\Filament\Clusters\Accounting\Resources\JournalEntries\JournalEntryResource;

class EditJournalEntry extends EditRecord
{
    protected static string $resource = JournalEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn () => $this->record->canBeDeleted()),
        ];
    }

    protected function afterSave(): void
    {
        // Update totals after saving lines
        $this->record->updateTotals();
    }

    protected function beforeFill(): void
    {
        if (! $this->record->canBeEdited()) {
            $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
        }
    }
}
