<?php

namespace Gopos\Policies;

use Gopos\Policies\Concerns\ChecksPermissions;

class DepartmentPolicy
{
    use ChecksPermissions;

    protected string $module = 'hr';
}
