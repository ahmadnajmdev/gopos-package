<?php

namespace Gopos\Policies;

use Gopos\Models\Role;
use Gopos\Models\User;
use Gopos\Policies\Concerns\ChecksPermissions;

class RolePolicy
{
    use ChecksPermissions;

    protected string $module = 'settings';

    protected array $permissionMap = [
        'viewAny' => 'view',
        'view' => 'view',
        'create' => 'manage_roles',
        'update' => 'manage_roles',
        'delete' => 'manage_roles',
        'deleteAny' => 'manage_roles',
        'forceDelete' => 'manage_roles',
        'forceDeleteAny' => 'manage_roles',
        'restore' => 'manage_roles',
        'restoreAny' => 'manage_roles',
        'reorder' => 'manage_roles',
    ];

    public function update(User $user, Role $model): bool
    {
        if ($model->is_system) {
            return false;
        }

        return $this->checkPermission($user, 'update');
    }

    public function delete(User $user, Role $model): bool
    {
        if ($model->is_system) {
            return false;
        }

        return $this->checkPermission($user, 'delete');
    }
}
