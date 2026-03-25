<?php

namespace App\Filament\Owner\Resources\AmenityResource\Pages;

use App\Filament\Owner\Resources\AmenityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAmenities extends ListRecords
{
    protected static string $resource = AmenityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إضافة مرفق جديد'),
        ];
    }
}

