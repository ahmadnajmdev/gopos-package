<?php

namespace Gopos\Filament\Clusters\HumanResources\Resources\Positions\Pages;

use Filament\Resources\Pages\CreateRecord;
use Gopos\Filament\Clusters\HumanResources\Resources\Positions\PositionResource;

class CreatePosition extends CreateRecord
{
    protected static string $resource = PositionResource::class;
}
