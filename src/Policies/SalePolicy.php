<?php

namespace Gopos\Policies;

use Gopos\Policies\Concerns\ChecksPermissions;

class SalePolicy
{
    use ChecksPermissions;

    protected string $module = 'sales';
}
