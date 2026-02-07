<?php

namespace Gopos\Filament\Clusters\Inventory\Resources\Categories\Pages;

use Filament\Resources\Pages\CreateRecord;
use Gopos\Filament\Clusters\Inventory\Resources\Categories\CategoryResource;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;
}
