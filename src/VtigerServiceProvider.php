<?php

namespace Clystnet\Vtiger;

use Illuminate\Support\ServiceProvider;

class VtigerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/Config/config.php' => config_path('vtiger.php'),
        ], 'vtiger');
        
        // use the vendor configuration file as fallback
        $this->mergeConfigFrom(
            __DIR__ . '/Config/config.php', 'vtiger'
        );
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('clystnet-vtiger', function () {
            return new Vtiger();
        });

        config([
            'config/vtiger.php',
        ]);
    }
}
