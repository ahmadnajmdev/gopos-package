<?php

namespace Gopos\Policies;

use Gopos\Policies\Concerns\ChecksPermissions;

class HolidayPolicy
{
    use ChecksPermissions;

    protected string $module = 'hr';

    protected array $permissionMap = [
        'viewAny' => 'view_attendance',
        'view' => 'view_attendance',
        'create' => 'manage_holidays',
        'update' => 'manage_holidays',
        'delete' => 'manage_holidays',
        'deleteAny' => 'manage_holidays',
        'forceDelete' => 'manage_holidays',
        'forceDeleteAny' => 'manage_holidays',
        'restore' => 'manage_holidays',
        'restoreAny' => 'manage_holidays',
        'reorder' => 'manage_holidays',
    ];
}
