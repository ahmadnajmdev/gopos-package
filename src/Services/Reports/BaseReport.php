<?php

namespace Gopos\Services\Reports;

use Gopos\Models\Currency;
use Illuminate\Support\Collection;

abstract class BaseReport
{
    protected string $title;

    protected array $columns = [];

    protected bool $showTotals = false;

    protected array $totalColumns = [];

    abstract public function getData(string $startDate, string $endDate, ?int $branchId = null, bool $allBranches = false): Collection|array;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function shouldShowTotals(): bool
    {
        return $this->showTotals;
    }

    public function getTotalColumns(): array
    {
        return $this->totalColumns;
    }

    public function getReportType(): string
    {
        $className = class_basename($this);

        return strtolower(str_replace('Report', '', $className));
    }

    protected function formatValue($value, string $type = 'text', ?string $currency = null): string
    {
        return match ($type) {
            'currency' => number_format($value, 2).' '.($currency ?? Currency::getBaseCurrency()?->symbol),
            'number' => number_format($value, 2),
            'date' => $value,
            default => $value
        };
    }
}
