<?php

namespace App\Filament\Owner\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use App\Models\Chalet;
use App\Models\Booking;
use App\Models\Review;
use App\Models\Payment;

class OwnerStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $ownerId = Auth::id();

        // إحصائيات الشاليهات
        $totalChalets = Chalet::where('owner_id', $ownerId)->count();
        $activeChalets = Chalet::where('owner_id', $ownerId)->where('is_active', true)->count();
        $featuredChalets = Chalet::where('owner_id', $ownerId)->where('is_featured', true)->count();

        // إحصائيات الحجوزات
        $totalBookings = Booking::whereHas('chalet', function ($query) use ($ownerId) {
            $query->where('owner_id', $ownerId);
        })->count();

        $pendingBookings = Booking::whereHas('chalet', function ($query) use ($ownerId) {
            $query->where('owner_id', $ownerId);
        })->where('status', 'pending')->count();

        $confirmedBookings = Booking::whereHas('chalet', function ($query) use ($ownerId) {
            $query->where('owner_id', $ownerId);
        })->where('status', 'confirmed')->count();

        $completedBookings = Booking::whereHas('chalet', function ($query) use ($ownerId) {
            $query->where('owner_id', $ownerId);
        })->where('status', 'completed')->count();

        // إحصائيات المدفوعات
        $totalRevenue = Payment::whereHas('booking.chalet', function ($query) use ($ownerId) {
            $query->where('owner_id', $ownerId);
        })->where('status', 'completed')->sum('amount');

        // إحصائيات التقييمات
        $totalReviews = Review::whereHas('chalet', function ($query) use ($ownerId) {
            $query->where('owner_id', $ownerId);
        })->count();

        $averageRating = Review::whereHas('chalet', function ($query) use ($ownerId) {
            $query->where('owner_id', $ownerId);
        })->where('is_approved', true)->avg('rating');

        return [
            Stat::make('إجمالي الشاليهات', $totalChalets)
                ->description('نشط: ' . $activeChalets . ' | مميز: ' . $featuredChalets)
                ->descriptionIcon('heroicon-m-home')
                ->color('success'),

            Stat::make('إجمالي الحجوزات', $totalBookings)
                ->description('مؤكد: ' . $confirmedBookings . ' | في الانتظار: ' . $pendingBookings)
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),

            Stat::make('إجمالي الإيرادات', number_format($totalRevenue, 2) . ' ر.س')
                ->description('من الحجوزات المكتملة')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('متوسط التقييم', $averageRating ? number_format($averageRating, 1) . ' / 5' : 'لا يوجد')
                ->description('من ' . $totalReviews . ' تقييم')
                ->descriptionIcon('heroicon-m-star')
                ->color($averageRating >= 4 ? 'success' : ($averageRating >= 3 ? 'warning' : 'danger')),

            Stat::make('الحجوزات المكتملة', $completedBookings)
                ->description('حجوزات تمت بنجاح')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}

