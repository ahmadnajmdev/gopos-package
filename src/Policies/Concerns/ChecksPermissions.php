<?php

namespace Gopos\Policies\Concerns;

use Gopos\Models\User;
use Illuminate\Database\Eloquent\Model;

trait ChecksPermissions
{
    /**
     * Get the permission module name (e.g., 'inventory', 'sales', 'hr').
     * Override this method in the policy class to set the module.
     */
    protected function getModule(): string
    {
        return $this->module;
    }

    /**
     * Get the permission mapping for policy methods.
     * Override this method in the policy class to customize permission names.
     *
     * @return array<string, string>
     */
    protected function getPermissionMap(): array
    {
        return $this->permissionMap ?? [
            'viewAny' => 'view',
            'view' => 'view',
            'create' => 'create',
            'update' => 'edit',
            'delete' => 'delete',
            'deleteAny' => 'delete',
            'forceDelete' => 'delete',
            'forceDeleteAny' => 'delete',
            'restore' => 'edit',
            'restoreAny' => 'edit',
            'reorder' => 'edit',
        ];
    }

    /**
     * Get the full permission name for an action.
     */
    protected function getPermissionName(string $action): string
    {
        $map = $this->getPermissionMap();
        $permission = $map[$action] ?? $action;

        return "{$this->getModule()}.{$permission}";
    }

    /**
     * Check if user has permission for the given action.
     */
    protected function checkPermission(User $user, string $action): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->hasPermissionTo($this->getPermissionName($action));
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Model $model): bool
    {
        return $this->checkPermission($user, 'view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->checkPermission($user, 'create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Model $model): bool
    {
        return $this->checkPermission($user, 'update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Model $model): bool
    {
        return $this->checkPermission($user, 'delete');
    }

    /**
     * Determine whether the user can bulk delete models.
     */
    public function deleteAny(User $user): bool
    {
        return $this->checkPermission($user, 'deleteAny');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Model $model): bool
    {
        return $this->checkPermission($user, 'restore');
    }

    /**
     * Determine whether the user can bulk restore models.
     */
    public function restoreAny(User $user): bool
    {
        return $this->checkPermission($user, 'restoreAny');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Model $model): bool
    {
        return $this->checkPermission($user, 'forceDelete');
    }

    /**
     * Determine whether the user can bulk permanently delete models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $this->checkPermission($user, 'forceDeleteAny');
    }

    /**
     * Determine whether the user can reorder models.
     */
    public function reorder(User $user): bool
    {
        return $this->checkPermission($user, 'reorder');
    }
}
