<?php

namespace Gopos\Filament\Clusters\Settings\Resources\Roles\Pages;

use Filament\Resources\Pages\CreateRecord;
use Gopos\Filament\Clusters\Settings\Resources\Roles\RoleResource;
use Gopos\Models\Permission;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
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
