<?php

namespace Gopos\Policies;

use Gopos\Models\Permission;
use Gopos\Models\User;
use Gopos\Policies\Concerns\ChecksPermissions;

class PermissionPolicy
{
    use ChecksPermissions;

    protected string $module = 'settings';

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Permission $model): bool
    {
        return false;
    }

    public function delete(User $user, Permission $model): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
