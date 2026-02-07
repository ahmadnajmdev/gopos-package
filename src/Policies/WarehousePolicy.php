<?php

namespace Gopos\Policies;

use Gopos\Policies\Concerns\ChecksPermissions;

class WarehousePolicy
{
    use ChecksPermissions;

    protected string $module = 'inventory';

    protected array $permissionMap = [
        'viewAny' => 'view',
        'view' => 'view',
        'create' => 'manage_warehouses',
        'update' => 'manage_warehouses',
        'delete' => 'manage_warehouses',
        'deleteAny' => 'manage_warehouses',
        'forceDelete' => 'manage_warehouses',
        'forceDeleteAny' => 'manage_warehouses',
        'restore' => 'manage_warehouses',
        'restoreAny' => 'manage_warehouses',
        'reorder' => 'manage_warehouses',
    ];
}
