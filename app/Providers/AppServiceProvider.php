<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

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
    public function boot()
    {
        // Set the application timezone to Pakistan
        config(['app.timezone' => 'Asia/Karachi']);
        Carbon::setLocale('en');
        date_default_timezone_set('Asia/Karachi');
    }
}
