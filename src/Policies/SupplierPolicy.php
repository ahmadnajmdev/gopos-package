<?php

namespace Gopos\Policies;

use Gopos\Policies\Concerns\ChecksPermissions;

class SupplierPolicy
{
    use ChecksPermissions;

    protected string $module = 'suppliers';
}
