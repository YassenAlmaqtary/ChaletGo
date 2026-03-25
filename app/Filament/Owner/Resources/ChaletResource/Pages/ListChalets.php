<?php

namespace App\Filament\Owner\Resources\ChaletResource\Pages;

use App\Filament\Owner\Resources\ChaletResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListChalets extends ListRecords
{
    protected static string $resource = ChaletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إضافة شاليه جديد'),
        ];
    }
}





