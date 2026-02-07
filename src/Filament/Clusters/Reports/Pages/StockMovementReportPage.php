<?php

namespace Gopos\Filament\Clusters\Reports\Pages;

use BackedEnum;
use Gopos\Services\Reports\StockMovementReport;

class StockMovementReportPage extends BaseReportPage
{
    protected static ?int $navigationSort = 32;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected function getReportClass(): string
    {
        return StockMovementReport::class;
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Inventory Reports');
    }
}
