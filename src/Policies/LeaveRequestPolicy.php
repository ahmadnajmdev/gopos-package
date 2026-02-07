<?php

namespace Gopos\Policies;

use Gopos\Models\User;
use Gopos\Policies\Concerns\ChecksPermissions;

class LeaveRequestPolicy
{
    use ChecksPermissions;

    protected string $module = 'hr';

    protected array $permissionMap = [
        'viewAny' => 'view_leave',
        'view' => 'view_leave',
        'create' => 'manage_leave',
        'update' => 'manage_leave',
        'delete' => 'manage_leave',
        'deleteAny' => 'manage_leave',
        'forceDelete' => 'manage_leave',
        'forceDeleteAny' => 'manage_leave',
        'restore' => 'manage_leave',
        'restoreAny' => 'manage_leave',
        'reorder' => 'manage_leave',
    ];

    public function approve(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermissionTo('hr.approve_leave');
    }
}
