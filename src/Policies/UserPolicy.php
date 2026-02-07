<?php

namespace Gopos\Policies;

use Gopos\Models\User;
use Gopos\Policies\Concerns\ChecksPermissions;

class UserPolicy
{
    use ChecksPermissions;

    protected string $module = 'settings';

    protected array $permissionMap = [
        'viewAny' => 'view',
        'view' => 'view',
        'create' => 'manage_users',
        'update' => 'manage_users',
        'delete' => 'manage_users',
        'deleteAny' => 'manage_users',
        'forceDelete' => 'manage_users',
        'forceDeleteAny' => 'manage_users',
        'restore' => 'manage_users',
        'restoreAny' => 'manage_users',
        'reorder' => 'manage_users',
    ];

    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }

        return $this->checkPermission($user, 'delete');
    }
}
