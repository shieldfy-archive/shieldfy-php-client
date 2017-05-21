<?php
namespace Shieldfy\Callbacks;

use Shieldfy\Config;
use Shieldfy\Cache\CacheInterface;

abstract class Callback
{
    protected $config;
    protected $cache;

    public function __construct(Config $config,CacheInterface $cache)
    {
        $this->config = $config;
        $this->cache  = $cache;
    }

    abstract public function handle();
}
