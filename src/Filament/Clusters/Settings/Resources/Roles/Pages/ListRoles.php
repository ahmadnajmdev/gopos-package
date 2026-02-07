<?php

namespace Gopos\Filament\Clusters\Settings\Resources\Roles\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Gopos\Filament\Clusters\Settings\Resources\Roles\RoleResource;
use Illuminate\Database\Eloquent\Builder;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function modifyQueryUsing(Builder $query): Builder
    {
        return $query->withCount('permissions');
    }
}
