<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class BookingsChart extends ChartWidget
{
    protected static ?string $heading = 'الحجوزات الشهرية';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $now = Carbon::now();
        $months = [];
        $bookingCounts = [];

        // Get last 12 months data
        for ($i = 11; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $months[] = $month->format('M Y');

            $count = Booking::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();

            $bookingCounts[] = $count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'عدد الحجوزات',
                    'data' => $bookingCounts,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
