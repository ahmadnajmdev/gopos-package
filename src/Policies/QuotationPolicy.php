<?php

namespace Gopos\Policies;

use Gopos\Policies\Concerns\ChecksPermissions;

class QuotationPolicy
{
    use ChecksPermissions;

    protected string $module = 'quotations';
}
