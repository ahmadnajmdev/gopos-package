<?php

namespace Gopos\Filament\Clusters\Accounting\Resources\Accounts\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Gopos\Filament\Clusters\Accounting\Resources\Accounts\AccountResource;

class EditAccount extends EditRecord
{
    protected static string $resource = AccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function () {
                    if (! $this->record->canBeDeleted()) {
                        throw new \Exception(__('This account cannot be deleted.'));
                    }
                }),
        ];
    }
}
