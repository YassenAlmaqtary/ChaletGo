<?php

namespace App\Filament\Owner\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;

class BookingsChartWidget extends ChartWidget
{
    protected static ?string $heading = 'إحصائيات الحجوزات (آخر 6 أشهر)';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $ownerId = Auth::id();

        // الحصول على بيانات الحجوزات للـ 6 أشهر الماضية
        $bookings = Booking::whereHas('chalet', function ($query) use ($ownerId) {
            $query->where('owner_id', $ownerId);
        })
        ->where('created_at', '>=', now()->subMonths(6))
        ->select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('COUNT(*) as count')
        )
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        $labels = [];
        $data = [];

        // ملء البيانات للـ 6 أشهر الماضية
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $monthLabel = now()->subMonths($i)->format('M Y');
            
            $labels[] = $monthLabel;
            $bookingCount = $bookings->firstWhere('month', $month);
            $data[] = $bookingCount ? $bookingCount->count : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'عدد الحجوزات',
                    'data' => $data,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}

