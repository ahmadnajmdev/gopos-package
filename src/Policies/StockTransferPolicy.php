<?php

namespace Gopos\Policies;

use Gopos\Policies\Concerns\ChecksPermissions;

class StockTransferPolicy
{
    use ChecksPermissions;

    protected string $module = 'inventory';

    protected array $permissionMap = [
        'viewAny' => 'view',
        'view' => 'view',
        'create' => 'transfer',
        'update' => 'transfer',
        'delete' => 'transfer',
        'deleteAny' => 'transfer',
        'forceDelete' => 'transfer',
        'forceDeleteAny' => 'transfer',
        'restore' => 'transfer',
        'restoreAny' => 'transfer',
        'reorder' => 'transfer',
    ];
}
