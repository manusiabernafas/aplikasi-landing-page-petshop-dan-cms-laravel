<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        // Batas per fitur (penghitung terpisah agar cek booking tidak menghabiskan jatah booking, dst.)
        RateLimiter::for('booking', fn (Request $r) => Limit::perMinutes(10, 6)->by('booking|' . $r->ip()));
        RateLimiter::for('cek', fn (Request $r) => Limit::perMinutes(10, 20)->by('cek|' . $r->ip()));
        RateLimiter::for('slot', fn (Request $r) => Limit::perMinute(60)->by('slot|' . $r->ip()));
    }
}
