<?php

namespace Gopos\Filament\Clusters\Reports\Pages;

use BackedEnum;
use Gopos\Services\Reports\TopCustomersReport;

class TopCustomersReportPage extends BaseReportPage
{
    protected static ?int $navigationSort = 42;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-star';

    protected function getReportClass(): string
    {
        return TopCustomersReport::class;
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Customer Reports');
    }
}
