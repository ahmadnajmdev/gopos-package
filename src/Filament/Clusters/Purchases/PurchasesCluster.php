<?php

namespace Gopos\Filament\Clusters\Purchases;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class PurchasesCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('Purchases');
    }

    public static function getClusterBreadcrumb(): string
    {
        return __('Purchases');
    }
}
