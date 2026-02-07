<?php

namespace Gopos\Policies;

use Gopos\Models\InventoryMovement;
use Gopos\Models\User;
use Gopos\Policies\Concerns\ChecksPermissions;

class InventoryMovementPolicy
{
    use ChecksPermissions;

    protected string $module = 'inventory';

    protected array $permissionMap = [
        'viewAny' => 'view',
        'view' => 'view',
        'create' => 'view',
        'update' => 'view',
        'delete' => 'view',
        'deleteAny' => 'view',
        'forceDelete' => 'view',
        'forceDeleteAny' => 'view',
        'restore' => 'view',
        'restoreAny' => 'view',
        'reorder' => 'view',
    ];

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, InventoryMovement $model): bool
    {
        return false;
    }

    public function delete(User $user, InventoryMovement $model): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
