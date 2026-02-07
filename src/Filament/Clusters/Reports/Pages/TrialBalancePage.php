<?php

namespace Gopos\Filament\Clusters\Reports\Pages;

use BackedEnum;
use Gopos\Services\Reports\TrialBalanceReport;

class TrialBalancePage extends BaseReportPage
{
    protected static ?int $navigationSort = 53;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-list-bullet';

    protected function getReportClass(): string
    {
        return TrialBalanceReport::class;
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Financial Reports');
    }

    public static function getNavigationLabel(): string
    {
        return __('Trial Balance');
    }
}
