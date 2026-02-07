<?php

namespace Gopos\Policies;

use Gopos\Policies\Concerns\ChecksPermissions;

class CurrencyPolicy
{
    use ChecksPermissions;

    protected string $module = 'accounting';

    protected array $permissionMap = [
        'viewAny' => 'view',
        'view' => 'view',
        'create' => 'manage_accounts',
        'update' => 'manage_accounts',
        'delete' => 'manage_accounts',
        'deleteAny' => 'manage_accounts',
        'forceDelete' => 'manage_accounts',
        'forceDeleteAny' => 'manage_accounts',
        'restore' => 'manage_accounts',
        'restoreAny' => 'manage_accounts',
        'reorder' => 'manage_accounts',
    ];
}
