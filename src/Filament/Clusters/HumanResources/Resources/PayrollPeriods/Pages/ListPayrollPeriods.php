<?php

namespace Gopos\Filament\Clusters\HumanResources\Resources\PayrollPeriods\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Gopos\Filament\Clusters\HumanResources\Resources\PayrollPeriods\PayrollPeriodResource;
use Illuminate\Database\Eloquent\Builder;

class ListPayrollPeriods extends ListRecords
{
    protected static string $resource = PayrollPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function modifyQueryUsing(Builder $query): Builder
    {
        return $query->withCount('payslips');
    }
}
