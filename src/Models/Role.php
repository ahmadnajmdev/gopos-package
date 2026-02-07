<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'name_ar',
        'name_ckb',
        'guard_name',
        'description',
        'description_ar',
        'description_ckb',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    /**
     * Get the permissions that belong to the role.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions');
    }

    /**
     * Get the users that have this role.
     */
    public function users(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'model', 'model_has_roles');
    }

    /**
     * Check if role has a specific permission.
     */
    public function hasPermissionTo(string|Permission $permission): bool
    {
        if (is_string($permission)) {
            return $this->permissions->contains('name', $permission);
        }

        return $this->permissions->contains($permission);
    }

    /**
     * Give permission to the role.
     */
    public function givePermissionTo(string|Permission ...$permissions): self
    {
        $permissionModels = collect($permissions)->map(function ($permission) {
            if (is_string($permission)) {
                return Permission::where('name', $permission)->first();
            }

            return $permission;
        })->filter();

        $this->permissions()->syncWithoutDetaching($permissionModels->pluck('id'));

        return $this->load('permissions');
    }

    /**
     * Revoke permission from the role.
     */
    public function revokePermissionTo(string|Permission ...$permissions): self
    {
        $permissionModels = collect($permissions)->map(function ($permission) {
            if (is_string($permission)) {
                return Permission::where('name', $permission)->first();
            }

            return $permission;
        })->filter();

        $this->permissions()->detach($permissionModels->pluck('id'));

        return $this->load('permissions');
    }

    /**
     * Sync permissions for the role.
     */
    public function syncPermissions(array $permissions): self
    {
        $permissionIds = collect($permissions)->map(function ($permission) {
            if (is_string($permission)) {
                return Permission::where('name', $permission)->first()?->id;
            }

            return $permission instanceof Permission ? $permission->id : $permission;
        })->filter();

        $this->permissions()->sync($permissionIds);

        return $this->load('permissions');
    }

    /**
     * Get localized name based on current locale.
     */
    public function getLocalizedNameAttribute(): string
    {
        $locale = app()->getLocale();

        return match ($locale) {
            'ar' => $this->name_ar ?: $this->name,
            'ckb' => $this->name_ckb ?: $this->name_ar ?: $this->name,
            default => $this->name,
        };
    }

    /**
     * Get localized description based on current locale.
     */
    public function getLocalizedDescriptionAttribute(): ?string
    {
        $locale = app()->getLocale();

        return match ($locale) {
            'ar' => $this->description_ar ?: $this->description,
            'ckb' => $this->description_ckb ?: $this->description_ar ?: $this->description,
            default => $this->description,
        };
    }

    /**
     * Find role by name.
     */
    public static function findByName(string $name, ?string $guardName = 'web'): ?self
    {
        return static::where('name', $name)
            ->where('guard_name', $guardName)
            ->first();
    }

    /**
     * Find or create role by name.
     */
    public static function findOrCreate(string $name, ?string $guardName = 'web'): self
    {
        return static::firstOrCreate(
            ['name' => $name, 'guard_name' => $guardName],
            ['name' => $name, 'guard_name' => $guardName]
        );
    }
}
