<?php

namespace Gopos\Filament\Clusters\Settings\Resources\Branches\Pages;

use Filament\Resources\Pages\CreateRecord;
use Gopos\Filament\Clusters\Settings\Resources\Branches\BranchResource;

class CreateBranch extends CreateRecord
{
    protected static string $resource = BranchResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
