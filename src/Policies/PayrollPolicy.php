<?php

namespace Gopos\Policies;

use Gopos\Policies\Concerns\ChecksPermissions;

class PayrollPolicy
{
    use ChecksPermissions;

    protected string $module = 'hr';
}
