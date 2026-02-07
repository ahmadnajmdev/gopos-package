<?php

namespace Gopos\Policies;

use Gopos\Policies\Concerns\ChecksPermissions;

class AttendancePolicy
{
    use ChecksPermissions;

    protected string $module = 'hr';

    protected array $permissionMap = [
        'viewAny' => 'view_attendance',
        'view' => 'view_attendance',
        'create' => 'manage_attendance',
        'update' => 'manage_attendance',
        'delete' => 'manage_attendance',
        'deleteAny' => 'manage_attendance',
        'forceDelete' => 'manage_attendance',
        'forceDeleteAny' => 'manage_attendance',
        'restore' => 'manage_attendance',
        'restoreAny' => 'manage_attendance',
        'reorder' => 'manage_attendance',
    ];
}
