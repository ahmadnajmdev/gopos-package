<?php

namespace Gopos\Filament\Clusters\Inventory;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class InventoryCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('Inventory');
    }

    public static function getClusterBreadcrumb(): string
    {
        return __('Inventory');
    }
}
