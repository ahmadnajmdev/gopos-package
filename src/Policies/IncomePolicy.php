<?php

namespace Gopos\Policies;

use Gopos\Policies\Concerns\ChecksPermissions;

class IncomePolicy
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
}
