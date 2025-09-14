<?php

namespace App\Filament\Resources\ChaletImageResource\Pages;

use App\Filament\Resources\ChaletImageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditChaletImage extends EditRecord
{
    protected static string $resource = ChaletImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
