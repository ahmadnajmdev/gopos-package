<?php

namespace Gopos\Policies;

use Gopos\Policies\Concerns\ChecksPermissions;

class PurchasePolicy
{
    use ChecksPermissions;

    protected string $module = 'purchases';
}
