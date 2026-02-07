<?php

namespace Gopos\Policies;

use Gopos\Policies\Concerns\ChecksPermissions;

class PayrollComponentPolicy
{
    use ChecksPermissions;

    protected string $module = 'hr';

    protected array $permissionMap = [
        'viewAny' => 'view_payroll',
        'view' => 'view_payroll',
        'create' => 'manage_components',
        'update' => 'manage_components',
        'delete' => 'manage_components',
        'deleteAny' => 'manage_components',
        'forceDelete' => 'manage_components',
        'forceDeleteAny' => 'manage_components',
        'restore' => 'manage_components',
        'restoreAny' => 'manage_components',
        'reorder' => 'manage_components',
    ];
}
