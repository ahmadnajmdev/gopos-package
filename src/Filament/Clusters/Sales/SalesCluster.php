<?php

namespace Gopos\Filament\Clusters\Sales;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class SalesCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('Sales');
    }

    public static function getClusterBreadcrumb(): string
    {
        return __('Sales');
    }
}
