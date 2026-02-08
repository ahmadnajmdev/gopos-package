<?php

namespace Gopos\Filament\Clusters\Hr;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class HrCluster extends Cluster
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
