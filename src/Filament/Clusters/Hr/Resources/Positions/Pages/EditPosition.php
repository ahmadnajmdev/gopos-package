<?php

namespace Gopos\Filament\Clusters\Hr\Resources\Positions\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Gopos\Filament\Clusters\Hr\Resources\Positions\PositionResource;

class EditPosition extends EditRecord
{
    protected static string $resource = PositionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
