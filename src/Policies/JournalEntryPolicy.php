<?php

namespace Gopos\Policies;

use Gopos\Models\User;
use Gopos\Policies\Concerns\ChecksPermissions;

class JournalEntryPolicy
{
    use ChecksPermissions;

    protected string $module = 'accounting';

    protected array $permissionMap = [
        'viewAny' => 'view',
        'view' => 'view',
        'create' => 'create_journal',
        'update' => 'create_journal',
        'delete' => 'void_journal',
        'deleteAny' => 'void_journal',
        'forceDelete' => 'void_journal',
        'forceDeleteAny' => 'void_journal',
        'restore' => 'create_journal',
        'restoreAny' => 'create_journal',
        'reorder' => 'create_journal',
    ];

    public function post(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermissionTo('accounting.post_journal');
    }

    public function void(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermissionTo('accounting.void_journal');
    }
}
