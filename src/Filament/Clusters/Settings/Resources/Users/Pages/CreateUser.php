<?php

namespace Gopos\Filament\Clusters\Settings\Resources\Users\Pages;

use Filament\Resources\Pages\CreateRecord;
use Gopos\Filament\Clusters\Settings\Resources\Users\UserResource;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        // User created
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
