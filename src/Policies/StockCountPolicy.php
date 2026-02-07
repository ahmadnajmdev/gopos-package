<?php

namespace Gopos\Policies;

use Gopos\Policies\Concerns\ChecksPermissions;

class StockCountPolicy
{
    use ChecksPermissions;

    protected string $module = 'inventory';

    protected array $permissionMap = [
        'viewAny' => 'view',
        'view' => 'view',
        'create' => 'adjust_stock',
        'update' => 'adjust_stock',
        'delete' => 'adjust_stock',
        'deleteAny' => 'adjust_stock',
        'forceDelete' => 'adjust_stock',
        'forceDeleteAny' => 'adjust_stock',
        'restore' => 'adjust_stock',
        'restoreAny' => 'adjust_stock',
        'reorder' => 'adjust_stock',
    ];
}
