<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator as PaginationPaginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        PaginationPaginator::useBootstrap();
     // Set timezone dynamically from settings - hanya jika aplikasi sudah booted
    try {
        if (app()->isBooted()) {
            $timezone = setting('timezone', 'Asia/Jakarta');
            config(['app.timezone' => $timezone]);
            date_default_timezone_set($timezone);
        }
    } catch (Exception $e) {
        // Fallback ke default timezone
        config(['app.timezone' => 'Asia/Jakarta']);
        date_default_timezone_set('Asia/Jakarta');
    }
    
    // Share settings with all views - hanya jika request tersedia
    view()->composer('*', function ($view) {
        try {
            $view->with([
                'app_name' => setting('app_name', 'Presensi Online'),
                'company_name' => setting('company_name', 'SMKN 2 Bandung'),
            ]);
        } catch (Exception $e) {
            $view->with([
                'app_name' => 'Presensi Online',
                'company_name' => 'SMKN 2 Bandung',
            ]);
        }
    });
    }
}
