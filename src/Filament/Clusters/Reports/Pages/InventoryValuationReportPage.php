<?php

namespace Gopos\Filament\Clusters\Reports\Pages;

use BackedEnum;
use Gopos\Services\Reports\InventoryValuationReport;

class InventoryValuationReportPage extends BaseReportPage
{
    protected static ?int $navigationSort = 31;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calculator';

    protected function getReportClass(): string
    {
        return InventoryValuationReport::class;
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Inventory Reports');
    }
}
