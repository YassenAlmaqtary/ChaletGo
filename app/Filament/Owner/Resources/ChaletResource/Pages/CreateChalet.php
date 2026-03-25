<?php

namespace App\Filament\Owner\Resources\ChaletResource\Pages;

use App\Filament\Owner\Resources\ChaletResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateChalet extends CreateRecord
{
    protected static string $resource = ChaletResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // تأكد من ربط الشاليه بالمالك الحالي
        $data['owner_id'] = Auth::id();
        
        // تعيين القيم الافتراضية
        if (!isset($data['is_active'])) {
            $data['is_active'] = true;
        }
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}





