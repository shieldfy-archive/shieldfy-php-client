<?php

namespace Shieldfy\Cache;

interface CacheInterface
{
    public function __construct($config);

    public function has($key);

    public function set($key, $value);

    public function get($key);
}