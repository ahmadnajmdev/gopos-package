<?php

namespace Gopos\Filament\Clusters\Reports\Pages;

use BackedEnum;
use Gopos\Services\Reports\PurchasesReport;

class PurchasesReportPage extends BaseReportPage
{
    protected static ?int $navigationSort = 21;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected function getReportClass(): string
    {
        return PurchasesReport::class;
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Purchase Reports');
    }
}
