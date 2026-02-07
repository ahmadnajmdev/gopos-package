<?php

namespace Gopos\Policies;

use Gopos\Policies\Concerns\ChecksPermissions;

class DepartmentPolicy
{
    use ChecksPermissions;

    protected string $module = 'hr';

    protected array $permissionMap = [
        'viewAny' => 'view_employees',
        'view' => 'view_employees',
        'create' => 'manage_departments',
        'update' => 'manage_departments',
        'delete' => 'manage_departments',
        'deleteAny' => 'manage_departments',
        'forceDelete' => 'manage_departments',
        'forceDeleteAny' => 'manage_departments',
        'restore' => 'manage_departments',
        'restoreAny' => 'manage_departments',
        'reorder' => 'manage_departments',
    ];
}
