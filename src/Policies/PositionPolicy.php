<?php

namespace Gopos\Policies;

use Gopos\Policies\Concerns\ChecksPermissions;

class PositionPolicy
{
    use ChecksPermissions;

    protected string $module = 'hr';
}
