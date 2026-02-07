<?php

namespace Gopos\Policies;

use Gopos\Policies\Concerns\ChecksPermissions;

class WorkSchedulePolicy
{
    use ChecksPermissions;

    protected string $module = 'hr';

    protected array $permissionMap = [
        'viewAny' => 'view_attendance',
        'view' => 'view_attendance',
        'create' => 'manage_schedules',
        'update' => 'manage_schedules',
        'delete' => 'manage_schedules',
        'deleteAny' => 'manage_schedules',
        'forceDelete' => 'manage_schedules',
        'forceDeleteAny' => 'manage_schedules',
        'restore' => 'manage_schedules',
        'restoreAny' => 'manage_schedules',
        'reorder' => 'manage_schedules',
    ];
}
