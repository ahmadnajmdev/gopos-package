<?php

namespace Gopos\Filament\Clusters\Accounting\Resources\AuditLogs\Pages;

use Filament\Resources\Pages\ListRecords;
use Gopos\Filament\Clusters\Accounting\Resources\AuditLogs\AuditLogResource;

class ListAuditLogs extends ListRecords
{
    protected static string $resource = AuditLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
