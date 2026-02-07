<?php

namespace Gopos\Policies;

use Gopos\Policies\Concerns\ChecksPermissions;

class ProductPolicy
{
    use ChecksPermissions;

    protected string $module = 'inventory';
}
