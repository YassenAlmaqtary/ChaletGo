<?php

namespace App\Filament\Widgets;

use App\Models\Chalet;
use App\Models\Booking;
use App\Models\User;
use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('إجمالي الشاليات', Chalet::count())
                ->description('عدد الشاليات المسجلة')
                ->descriptionIcon('heroicon-m-home')
                ->color('success'),

            Stat::make('الشاليات النشطة', Chalet::where('is_active', true)->count())
                ->description('الشاليات المتاحة للحجز')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('primary'),

            Stat::make('إجمالي الحجوزات', Booking::count())
                ->description('جميع الحجوزات')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning'),

            Stat::make('الحجوزات النشطة', Booking::whereIn('status', ['pending', 'confirmed'])->count())
                ->description('الحجوزات المؤكدة والمعلقة')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),

            Stat::make('إجمالي المستخدمين', User::count())
                ->description('جميع المستخدمين')
                ->descriptionIcon('heroicon-m-users')
                ->color('gray'),

            Stat::make('إجمالي الإيرادات', 'ريال ' . number_format(Payment::where('status', 'completed')->sum('amount'), 2))
                ->description('المدفوعات المكتملة')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
