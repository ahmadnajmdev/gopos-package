<?php

namespace Gopos\Policies;

use Gopos\Models\AuditLog;
use Gopos\Models\User;
use Gopos\Policies\Concerns\ChecksPermissions;

class AuditLogPolicy
{
    use ChecksPermissions;

    protected string $module = 'accounting';

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, AuditLog $model): bool
    {
        return false;
    }

    public function delete(User $user, AuditLog $model): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
