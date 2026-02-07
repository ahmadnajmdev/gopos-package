<?php

namespace Gopos\Policies;

use Gopos\Policies\Concerns\ChecksPermissions;

class CustomerPolicy
{
    use ChecksPermissions;

    protected string $module = 'customers';
}
