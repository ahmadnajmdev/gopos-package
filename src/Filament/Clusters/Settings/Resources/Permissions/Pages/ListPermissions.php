<?php

namespace Gopos\Filament\Clusters\Settings\Resources\Permissions\Pages;

use Filament\Resources\Pages\ListRecords;
use Gopos\Filament\Clusters\Settings\Resources\Permissions\PermissionResource;

class ListPermissions extends ListRecords
{
    protected static string $resource = PermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
