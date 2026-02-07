<?php

namespace Gopos\Filament\Clusters\Settings\Resources\Roles\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Gopos\Filament\Clusters\Settings\Resources\Roles\RoleResource;

class ViewRole extends ViewRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make()
                ->hidden(fn () => $this->record->is_system),
        ];
    }
}
