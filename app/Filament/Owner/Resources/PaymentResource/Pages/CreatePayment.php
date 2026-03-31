<?php

namespace App\Filament\Owner\Resources\PaymentResource\Pages;

use App\Filament\Owner\Resources\PaymentResource;
use App\Models\Booking;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getTitle(): string
    {
        return 'إضافة دفعة';
    }

    protected function fillForm(): void
    {
        $this->callHook('beforeFill');

        $data = [
            'payment_method' => 'cash',
            'status' => 'completed',
            'paid_at' => now(),
        ];

        if ($bookingId = request()->query('booking_id')) {
            $id = (int) $bookingId;
            $booking = Booking::with('chalet')->find($id);
            if ($booking && (int) $booking->chalet->owner_id === (int) Auth::id()) {
                $data['booking_id'] = $id;
                $data['amount'] = $booking->final_amount;
            }
        }

        $this->form->fill($data);

        $this->callHook('afterFill');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $booking = Booking::with('chalet')->find($data['booking_id'] ?? null);

        if (! $booking || (int) $booking->chalet->owner_id !== (int) Auth::id()) {
            throw ValidationException::withMessages([
                'booking_id' => 'لا يمكن تسجيل دفعة لهذا الحجز.',
            ]);
        }

        if ($booking->status !== Booking::STATUS_CONFIRMED) {
            throw ValidationException::withMessages([
                'booking_id' => 'يُسمح بإضافة دفعة للحجوزات المؤكدة فقط.',
            ]);
        }

        if ($booking->isPaid()) {
            throw ValidationException::withMessages([
                'booking_id' => 'هذا الحجز لديه دفعة مكتملة بالفعل.',
            ]);
        }

        if (empty($data['transaction_id'])) {
            $prefix = match ($data['payment_method'] ?? 'cash') {
                'credit_card' => 'CC',
                'bank_transfer' => 'BT',
                'digital_wallet' => 'DW',
                'cash' => 'CASH',
                default => 'PAY',
            };
            $data['transaction_id'] = $prefix.'_'.time().'_'.random_int(1000, 9999);
        }

        if (empty($data['paid_at'])) {
            $data['paid_at'] = now();
        }

        return $data;
    }
}

