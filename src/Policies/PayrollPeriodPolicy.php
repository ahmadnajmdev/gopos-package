<?php

namespace Gopos\Policies;

use Gopos\Models\User;
use Gopos\Policies\Concerns\ChecksPermissions;

class PayrollPeriodPolicy
{
    use ChecksPermissions;

    protected string $module = 'hr';

    protected array $permissionMap = [
        'viewAny' => 'view_payroll',
        'view' => 'view_payroll',
        'create' => 'process_payroll',
        'update' => 'process_payroll',
        'delete' => 'process_payroll',
        'deleteAny' => 'process_payroll',
        'forceDelete' => 'process_payroll',
        'forceDeleteAny' => 'process_payroll',
        'restore' => 'process_payroll',
        'restoreAny' => 'process_payroll',
        'reorder' => 'process_payroll',
    ];

    public function approve(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermissionTo('hr.approve_payroll');
    }
}
