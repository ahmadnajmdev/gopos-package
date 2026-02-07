<?php

namespace Gopos\Policies;

use Gopos\Policies\Concerns\ChecksPermissions;

class SaleReturnPolicy
{
    use ChecksPermissions;

    protected string $module = 'sales';
}
