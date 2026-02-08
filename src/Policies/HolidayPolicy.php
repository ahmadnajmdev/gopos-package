<?php

namespace Gopos\Policies;

use Gopos\Policies\Concerns\ChecksPermissions;

class HolidayPolicy
{
    use ChecksPermissions;

    protected string $module = 'hr';
}
