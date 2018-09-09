<?php
namespace Shieldfy\Callbacks;

use Shieldfy\Config;
use Shieldfy\Http\Dispatcher;

abstract class Callback
{
    protected $config;

    public function __construct(Config $config, Dispatcher $dispatcher)
    {
        $this->config = $config;
        $this->dispatcher = $dispatcher;
    }

    abstract public function handle();
}
