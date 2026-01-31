<?php

namespace App\Filament\Owner\Resources\ChaletImageResource\Pages;

use App\Filament\Owner\Resources\ChaletImageResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\ChaletImage;

class CreateChaletImage extends CreateRecord
{
    protected static string $resource = ChaletImageResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // تعيين ترتيب العرض تلقائياً إذا لم يتم تحديده
        if (!isset($data['sort_order']) || $data['sort_order'] === null) {
            $chaletId = $data['chalet_id'] ?? null;
            if ($chaletId) {
                $maxOrder = ChaletImage::where('chalet_id', $chaletId)->max('sort_order') ?? 0;
                $data['sort_order'] = $maxOrder + 1;
            } else {
                $data['sort_order'] = 0;
            }
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

