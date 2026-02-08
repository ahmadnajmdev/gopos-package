<?php

namespace Gopos\Policies;

use Gopos\Policies\Concerns\ChecksPermissions;

class BranchPolicy
{
    use ChecksPermissions;

    protected string $module = 'branches';
}
