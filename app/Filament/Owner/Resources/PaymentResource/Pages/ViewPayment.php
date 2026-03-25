<?php

namespace App\Filament\Owner\Resources\PaymentResource\Pages;

use App\Filament\Owner\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPayment extends ViewRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('تعديل')
                ->visible(fn ($record) => $record->status !== 'completed' && $record->status !== 'refunded'),
        ];
    }

    public function getTitle(): string
    {
        return 'عرض المدفوعة';
    }
}





