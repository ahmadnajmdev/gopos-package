<?php

namespace Gopos\Policies;

use Gopos\Policies\Concerns\ChecksPermissions;

class EmployeePolicy
{
    use ChecksPermissions;

    protected string $module = 'hr';
}
