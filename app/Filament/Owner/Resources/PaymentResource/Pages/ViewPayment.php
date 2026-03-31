<?php

namespace App\Filament\Owner\Resources\PaymentResource\Pages;

use App\Filament\Owner\Resources\PaymentResource;
use App\Models\Payment;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewPayment extends ViewRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('تعديل')
                ->visible(fn (Payment $record) => $record->status !== 'completed' && $record->status !== 'refunded'),
            Actions\Action::make('refund')
                ->label('استرداد')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('استرداد المدفوعة')
                ->modalDescription('هل أنت متأكد من استرداد هذه المدفوعة؟')
                ->modalSubmitActionLabel('استرداد')
                ->visible(fn (Payment $record) => $record->status === 'completed')
                ->action(function (Payment $record): void {
                    $record->update(['status' => 'refunded']);
                    Notification::make()
                        ->title('تم استرداد المدفوعة')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function getTitle(): string
    {
        return 'عرض المدفوعة';
    }
}





