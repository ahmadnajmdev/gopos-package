<?php

namespace Gopos\Policies;

use Gopos\Policies\Concerns\ChecksPermissions;

class PurchaseReturnPolicy
{
    use ChecksPermissions;

    protected string $module = 'purchases';
}
