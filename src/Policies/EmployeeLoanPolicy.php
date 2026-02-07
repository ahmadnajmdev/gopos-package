<?php

namespace Gopos\Policies;

use Gopos\Models\User;
use Gopos\Policies\Concerns\ChecksPermissions;

class EmployeeLoanPolicy
{
    use ChecksPermissions;

    protected string $module = 'hr';

    protected array $permissionMap = [
        'viewAny' => 'view_loans',
        'view' => 'view_loans',
        'create' => 'manage_loans',
        'update' => 'manage_loans',
        'delete' => 'manage_loans',
        'deleteAny' => 'manage_loans',
        'forceDelete' => 'manage_loans',
        'forceDeleteAny' => 'manage_loans',
        'restore' => 'manage_loans',
        'restoreAny' => 'manage_loans',
        'reorder' => 'manage_loans',
    ];

    public function approve(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermissionTo('hr.approve_loans');
    }
}
