<?php

namespace Gopos\Policies;

use Gopos\Policies\Concerns\ChecksPermissions;

class PositionPolicy
{
    use ChecksPermissions;

    protected string $module = 'hr';

    protected array $permissionMap = [
        'viewAny' => 'view_employees',
        'view' => 'view_employees',
        'create' => 'manage_positions',
        'update' => 'manage_positions',
        'delete' => 'manage_positions',
        'deleteAny' => 'manage_positions',
        'forceDelete' => 'manage_positions',
        'forceDeleteAny' => 'manage_positions',
        'restore' => 'manage_positions',
        'restoreAny' => 'manage_positions',
        'reorder' => 'manage_positions',
    ];
}
