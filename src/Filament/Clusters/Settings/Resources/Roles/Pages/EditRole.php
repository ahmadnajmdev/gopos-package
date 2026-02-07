<?php

namespace Gopos\Filament\Clusters\Settings\Resources\Roles\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Gopos\Filament\Clusters\Settings\Resources\Roles\RoleResource;
use Gopos\Models\Permission;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->hidden(fn () => $this->record->is_system),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        // Collect permissions from form state (non-dehydrated fields)
        $formState = $this->form->getRawState();
        $permissionIds = $this->collectPermissionIdsFromState($formState);
        $this->record->permissions()->sync($permissionIds);
    }

    protected function collectPermissionIdsFromState(array $state): array
    {
        $permissionIds = [];
        $modules = Permission::distinct('module')->pluck('module')->toArray();

        foreach ($modules as $module) {
            $key = "permissions_{$module}";
            if (isset($state[$key]) && is_array($state[$key])) {
                $permissionIds = array_merge($permissionIds, $state[$key]);
            }
        }

        return array_map('intval', array_filter($permissionIds));
    }
}
