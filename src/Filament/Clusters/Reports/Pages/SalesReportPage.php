<?php

namespace Gopos\Filament\Clusters\Reports\Pages;

use BackedEnum;
use Gopos\Services\Reports\SalesReport;

class SalesReportPage extends BaseReportPage
{
    protected static ?int $navigationSort = 11;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';

    protected function getReportClass(): string
    {
        return SalesReport::class;
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Sales Reports');
    }
}
