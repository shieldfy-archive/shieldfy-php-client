<?php
namespace Shieldfy\Extensions\Symfony;

use Shieldfy\Guard;

class ShieldfyBundle extends \Symfony\Component\HttpKernel\Bundle\Bundle
{
    public function __construct()
    {
        $this->runGuard();
    }

    public function runGuard()
    {
        if (php_sapi_name() === "cli") {
            return;
        }
        $shieldfy = Guard::init([
            'app_key' => getenv('SHIELDFY_APP_KEY'),
            'app_secret' => getenv('SHIELDFY_APP_SECRET')
        ]);
    }
}
