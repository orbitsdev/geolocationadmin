<?php

namespace App\Filament\Resources\CouncilPositionResource\Pages;

use App\Filament\Resources\CouncilPositionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCouncilPosition extends EditRecord
{
    protected static string $resource = CouncilPositionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
