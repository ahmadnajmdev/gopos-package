<?php

namespace Gopos\Policies;

use Gopos\Models\User;
use Gopos\Policies\Concerns\ChecksPermissions;

class EmployeePolicy
{
    use ChecksPermissions;

    protected string $module = 'hr';

    protected array $permissionMap = [
        'viewAny' => 'view_employees',
        'view' => 'view_employees',
        'create' => 'create_employees',
        'update' => 'edit_employees',
        'delete' => 'delete_employees',
        'deleteAny' => 'delete_employees',
        'forceDelete' => 'delete_employees',
        'forceDeleteAny' => 'delete_employees',
        'restore' => 'edit_employees',
        'restoreAny' => 'edit_employees',
        'reorder' => 'edit_employees',
    ];

    public function terminate(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermissionTo('hr.terminate_employees');
    }
}
