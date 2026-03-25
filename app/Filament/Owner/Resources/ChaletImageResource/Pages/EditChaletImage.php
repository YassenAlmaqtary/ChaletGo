<?php

namespace App\Filament\Owner\Resources\ChaletImageResource\Pages;

use App\Filament\Owner\Resources\ChaletImageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditChaletImage extends EditRecord
{
    protected static string $resource = ChaletImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('حذف'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

