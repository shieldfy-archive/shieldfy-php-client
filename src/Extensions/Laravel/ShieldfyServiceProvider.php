<?php
namespace Shieldfy\Extensions\Laravel;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Shieldfy\Extensions\Laravel\Middlewares\ShieldfyMiddleware;

class ShieldfyServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @param \Illuminate\Routing\Router $router
     *
     * @return void
     */
    public function boot(Router $router)
    {
        $this->loadViewsFrom(__DIR__.'/views', 'shieldfy');

        $this->publishes([
            __DIR__.'/views' => resource_path('views/vendor/shieldfy'),
        ], 'view');

        $this->publishes([
            __DIR__.'/config/shieldfy.php' => config_path('shieldfy.php'),
        ], 'config');

        //register middlewares
        $this->registerMiddleWare($router);
    }

    /**
     * register middleware.
     *
     * @param \Illuminate\Routing\Router $router
     *
     * @return void
     */
    protected function registerMiddleWare(Router $router)
    {
        // Append middleware to the 'web' middlware group
        $router->pushMiddlewareToGroup('web', ShieldfyMiddleware::class);
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
