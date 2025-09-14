<?php

namespace App\Filament\Resources\ChaletResource\Pages;

use App\Filament\Resources\ChaletResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditChalet extends EditRecord
{
    protected static string $resource = ChaletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
