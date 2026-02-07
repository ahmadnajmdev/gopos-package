<?php

namespace Gopos\Filament\Clusters\Accounting;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class AccountingCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return __('Accounting');
    }

    public static function getClusterBreadcrumb(): string
    {
        return __('Accounting');
    }
}
