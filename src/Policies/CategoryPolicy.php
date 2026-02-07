<?php

namespace Gopos\Policies;

use Gopos\Policies\Concerns\ChecksPermissions;

class CategoryPolicy
{
    use ChecksPermissions;

    protected string $module = 'inventory';
}
