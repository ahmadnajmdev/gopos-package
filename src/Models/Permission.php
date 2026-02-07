<?php

namespace Gopos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'name_ar',
        'name_ckb',
        'guard_name',
        'module',
        'description',
        'description_ar',
        'description_ckb',
    ];

    /**
     * Get the roles that have this permission.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_has_permissions');
    }

    /**
     * Get the users that have this permission directly.
     */
    public function users(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'model', 'model_has_permissions');
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
     * Find permission by name.
     */
    public static function findByName(string $name, ?string $guardName = 'web'): ?self
    {
        return static::where('name', $name)
            ->where('guard_name', $guardName)
            ->first();
    }

    /**
     * Find or create permission by name.
     */
    public static function findOrCreate(string $name, string $module, ?string $guardName = 'web'): self
    {
        return static::firstOrCreate(
            ['name' => $name, 'guard_name' => $guardName],
            ['name' => $name, 'module' => $module, 'guard_name' => $guardName]
        );
    }

    /**
     * Scope to filter by module.
     */
    public function scopeModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Get available modules.
     */
    public static function getModules(): array
    {
        return [
            'pos' => __('POS'),
            'inventory' => __('Inventory'),
            'sales' => __('Sales'),
            'purchases' => __('Purchases'),
            'customers' => __('Customers'),
            'suppliers' => __('Suppliers'),
            'accounting' => __('Accounting'),
            'hr' => __('HR'),
            'reports' => __('Reports'),
            'settings' => __('Settings'),
        ];
    }
}
