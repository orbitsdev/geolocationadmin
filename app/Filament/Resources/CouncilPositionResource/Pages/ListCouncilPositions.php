<?php

namespace App\Filament\Resources\CouncilPositionResource\Pages;

use App\Filament\Resources\CouncilPositionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCouncilPositions extends ListRecords
{
    protected static string $resource = CouncilPositionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
