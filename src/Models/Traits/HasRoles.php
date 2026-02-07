<?php

namespace Gopos\Models\Traits;

use Gopos\Models\Permission;
use Gopos\Models\Role;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;

trait HasRoles
{
    /**
     * Get all roles assigned to the model.
     */
    public function roles(): MorphToMany
    {
        return $this->morphToMany(Role::class, 'model', 'model_has_roles');
    }

    /**
     * Get all permissions assigned directly to the model.
     */
    public function permissions(): MorphToMany
    {
        return $this->morphToMany(Permission::class, 'model', 'model_has_permissions');
    }

    /**
     * Assign a role to the model.
     */
    public function assignRole(string|Role ...$roles): self
    {
        $roleModels = collect($roles)->map(function ($role) {
            if (is_string($role)) {
                return Role::findByName($role);
            }

            return $role;
        })->filter();

        $this->roles()->syncWithoutDetaching($roleModels->pluck('id'));

        return $this->load('roles');
    }

    /**
     * Remove a role from the model.
     */
    public function removeRole(string|Role ...$roles): self
    {
        $roleModels = collect($roles)->map(function ($role) {
            if (is_string($role)) {
                return Role::findByName($role);
            }

            return $role;
        })->filter();

        $this->roles()->detach($roleModels->pluck('id'));

        return $this->load('roles');
    }

    /**
     * Sync roles for the model.
     */
    public function syncRoles(array $roles): self
    {
        $roleIds = collect($roles)->map(function ($role) {
            if (is_string($role)) {
                return Role::findByName($role)?->id;
            }

            return $role instanceof Role ? $role->id : $role;
        })->filter();

        $this->roles()->sync($roleIds);

        return $this->load('roles');
    }

    /**
     * Check if model has a specific role.
     */
    public function hasRole(string|Role $role): bool
    {
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }

        return $this->roles->contains($role);
    }

    /**
     * Check if model has any of the given roles.
     */
    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if model has all of the given roles.
     */
    public function hasAllRoles(array $roles): bool
    {
        foreach ($roles as $role) {
            if (! $this->hasRole($role)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Give permission directly to the model.
     */
    public function givePermissionTo(string|Permission ...$permissions): self
    {
        $permissionModels = collect($permissions)->map(function ($permission) {
            if (is_string($permission)) {
                return Permission::findByName($permission);
            }

            return $permission;
        })->filter();

        $this->permissions()->syncWithoutDetaching($permissionModels->pluck('id'));

        return $this->load('permissions');
    }

    /**
     * Revoke permission from the model.
     */
    public function revokePermissionTo(string|Permission ...$permissions): self
    {
        $permissionModels = collect($permissions)->map(function ($permission) {
            if (is_string($permission)) {
                return Permission::findByName($permission);
            }

            return $permission;
        })->filter();

        $this->permissions()->detach($permissionModels->pluck('id'));

        return $this->load('permissions');
    }

    /**
     * Check if model has a specific permission (through roles or directly).
     */
    public function hasPermissionTo(string|Permission $permission): bool
    {
        $permissionName = is_string($permission) ? $permission : $permission->name;

        // Check direct permissions
        if ($this->permissions->contains('name', $permissionName)) {
            return true;
        }

        // Check wildcard permission (super_admin)
        if ($this->hasRole('super_admin')) {
            return true;
        }

        // Check permissions through roles
        foreach ($this->roles as $role) {
            if ($role->hasPermissionTo($permissionName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Alias for hasPermissionTo.
     */
    public function can($ability, $arguments = []): bool
    {
        // If it's a permission check (string starting with module.)
        if (is_string($ability) && str_contains($ability, '.')) {
            return $this->hasPermissionTo($ability);
        }

        // Fall back to Laravel's default authorization
        return parent::can($ability, $arguments);
    }

    /**
     * Check if model has any of the given permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermissionTo($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if model has all of the given permissions.
     */
    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (! $this->hasPermissionTo($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all permissions for the model (direct + through roles).
     */
    public function getAllPermissions(): Collection
    {
        $directPermissions = $this->permissions;
        $rolePermissions = $this->roles->flatMap(fn ($role) => $role->permissions);

        return $directPermissions->merge($rolePermissions)->unique('id');
    }

    /**
     * Get permission names for the model.
     */
    public function getPermissionNames(): Collection
    {
        return $this->getAllPermissions()->pluck('name');
    }

    /**
     * Get role names for the model.
     */
    public function getRoleNames(): Collection
    {
        return $this->roles->pluck('name');
    }

    /**
     * Check if user is super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }
}
