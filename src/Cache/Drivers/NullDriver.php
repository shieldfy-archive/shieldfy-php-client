<?php

namespace Shieldfy\Cache;

use Shieldfy\Cache\CacheInterface;

class NullDriver implements CacheInterface
{
    public function __construct($config){}

    public function has($key){}

    public function set($key, $value){}

    public function get($key){}
}
