<?php

namespace App\Filament\Owner\Resources\ChaletResource\Pages;

use App\Filament\Owner\Resources\ChaletResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditChalet extends EditRecord
{
    protected static string $resource = ChaletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('عرض'),
            Actions\DeleteAction::make()
                ->label('حذف'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}





