<?php

namespace App\Filament\Resources\ChaletImageResource\Pages;

use App\Filament\Resources\ChaletImageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListChaletImages extends ListRecords
{
    protected static string $resource = ChaletImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
