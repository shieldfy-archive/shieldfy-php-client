<?php

namespace Shieldfy\Extensions\Lumen;

use Illuminate\Support\ServiceProvider;
use Shieldfy\Extensions\Lumen\Middlewares\ShieldfyMiddleware;

class ShieldfyServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/views', 'shieldfy');

        $this->registerMiddleWare();
    }

    /**
     * register middleware.
     * @return void
     */
    protected function registerMiddleWare()
    {
        // Append middleware to the 'web' middlware group
        $this->app->middleware([
            ShieldfyMiddleware::class
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(__DIR__.'/config/shieldfy.php', 'shieldfy');
    }
}
