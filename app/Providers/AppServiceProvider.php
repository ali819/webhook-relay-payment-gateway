<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        // Fix mixed content saat menggunakan HTTPS di lingkungan lokal
        $host = request()->getHost();
        if (
            app()->environment('local') && // Pastikan hanya dalam lingkungan lokal
            !filter_var($host, FILTER_VALIDATE_IP) && // Pastikan host bukan IP
            $host !== 'localhost' && // Pastikan host bukan localhost
            !str_starts_with($host, '127.') // Pastikan host bukan loopback IP
        ) {
            URL::forceScheme('https');
        }
        
        Paginator::useBootstrapFive();
    }
}
