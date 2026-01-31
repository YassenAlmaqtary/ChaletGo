<?php

namespace App\Filament\Owner\Resources\AmenityResource\Pages;

use App\Filament\Owner\Resources\AmenityResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateAmenity extends CreateRecord
{
    protected static string $resource = AmenityResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // ربط المرفق بالمالك الحالي
        $data['owner_id'] = Auth::id();
        
        // تعيين القيم الافتراضية
        if (!isset($data['is_active'])) {
            $data['is_active'] = true;
        }
        
        if (!isset($data['category'])) {
            $data['category'] = 'general';
        }
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

