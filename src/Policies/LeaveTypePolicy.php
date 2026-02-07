<?php

namespace Gopos\Policies;

use Gopos\Policies\Concerns\ChecksPermissions;

class LeaveTypePolicy
{
    use ChecksPermissions;

    protected string $module = 'hr';

    protected array $permissionMap = [
        'viewAny' => 'view_leave',
        'view' => 'view_leave',
        'create' => 'manage_leave_types',
        'update' => 'manage_leave_types',
        'delete' => 'manage_leave_types',
        'deleteAny' => 'manage_leave_types',
        'forceDelete' => 'manage_leave_types',
        'forceDeleteAny' => 'manage_leave_types',
        'restore' => 'manage_leave_types',
        'restoreAny' => 'manage_leave_types',
        'reorder' => 'manage_leave_types',
    ];
}
