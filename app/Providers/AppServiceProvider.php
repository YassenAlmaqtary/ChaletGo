<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set application locale to Arabic
        app()->setLocale('ar');

        // Set Carbon locale for date formatting
        if (class_exists(\Carbon\Carbon::class)) {
            \Carbon\Carbon::setLocale('ar');
        }

        // Set HTML direction for RTL
        view()->share('htmlDir', 'rtl');
    }
}
