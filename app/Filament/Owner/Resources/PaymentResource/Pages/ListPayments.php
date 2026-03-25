<?php

namespace App\Filament\Owner\Resources\PaymentResource\Pages;

use App\Filament\Owner\Resources\PaymentResource;
use Filament\Resources\Pages\ListRecords;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    public function getTitle(): string
    {
        return 'مدفوعاتي';
    }
}





