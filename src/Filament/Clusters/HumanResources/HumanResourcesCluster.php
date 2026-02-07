<?php

namespace Gopos\Filament\Clusters\HumanResources;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class HumanResourcesCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?int $navigationSort = 5;

    public static function getNavigationLabel(): string
    {
        return __('Human Resources');
    }

    public static function getClusterBreadcrumb(): string
    {
        return __('Human Resources');
    }
}
