<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\TcpServer;


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
        // if (php_sapi_name() !== 'cli') {
        //     app(TcpServer::class)->start();
        // }
    }
}
