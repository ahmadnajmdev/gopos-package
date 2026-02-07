<?php

namespace Gopos\Filament\Clusters\Settings\Resources\Permissions\Pages;

use Filament\Resources\Pages\ViewRecord;
use Gopos\Filament\Clusters\Settings\Resources\Permissions\PermissionResource;

class ViewPermission extends ViewRecord
{
    protected static string $resource = PermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
