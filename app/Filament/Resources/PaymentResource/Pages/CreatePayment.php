<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Models\Booking;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

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
            $booking = Booking::query()->find($id);
            if ($booking) {
                $data['booking_id'] = $id;
                $data['amount'] = $booking->final_amount;
            }
        }

        $this->form->fill($data);

        $this->callHook('afterFill');
    }
}
