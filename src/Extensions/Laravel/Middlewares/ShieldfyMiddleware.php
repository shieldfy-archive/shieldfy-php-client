<?php

namespace Shieldfy\Extensions\Laravel\Middlewares;

use Closure;
use DB;
use View;
use Shieldfy\Guard;

class ShieldfyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $shieldfy = Guard::init([
                'endpoint'       => config('shieldfy.endpoint'),
                'app_key'        => config('shieldfy.keys.app_key', env('SHIELDFY_APP_KEY')),
                'app_secret'     => config('shieldfy.keys.app_secret', env('SHIELDFY_APP_SECRET')),
                'debug'          => config('shieldfy.debug'),
                'action'         => config('shieldfy.action'),
                'blockPage'      => ($blockPage = config('shieldfy.blockPage')) ? view($blockPage)->getPath() : null,
                'headers'        => config('shieldfy.headers'),
                'disable'        => config('shieldfy.disable'),
        ]);

        View::composer('*', function ($view) use ($shieldfy) {
            $shieldfy->events->trigger('view.render', [
                $view->getPath(),
                $view->getData()
            ]);
        });

        DB::listen(function ($query) use ($shieldfy) {
            $shieldfy->events->trigger('db.query', [$query->sql,$query->bindings]);
        });

        return $next($request);
    }
}
