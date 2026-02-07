<?php

namespace Gopos\Policies;

use Gopos\Policies\Concerns\ChecksPermissions;

class UnitPolicy
{
    use ChecksPermissions;

    protected string $module = 'inventory';
}
