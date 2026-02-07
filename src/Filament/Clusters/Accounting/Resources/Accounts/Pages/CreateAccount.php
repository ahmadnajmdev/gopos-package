<?php

namespace Gopos\Filament\Clusters\Accounting\Resources\Accounts\Pages;

use Filament\Resources\Pages\CreateRecord;
use Gopos\Filament\Clusters\Accounting\Resources\Accounts\AccountResource;

class CreateAccount extends CreateRecord
{
    protected static string $resource = AccountResource::class;
}
