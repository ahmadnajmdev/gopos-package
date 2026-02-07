<?php

namespace Gopos\Filament\Clusters\Reports\Pages;

use BackedEnum;
use Gopos\Services\Reports\SaleByProductReport;

class SaleByProductReportPage extends BaseReportPage
{
    protected static ?int $navigationSort = 12;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected function getReportClass(): string
    {
        return SaleByProductReport::class;
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Sales Reports');
    }
}
