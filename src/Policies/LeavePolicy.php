<?php

namespace Gopos\Policies;

use Gopos\Policies\Concerns\ChecksPermissions;

class LeavePolicy
{
    use ChecksPermissions;

    protected string $module = 'hr';
}
