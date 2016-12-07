<?php

namespace Shieldfy\Cache\Drivers;

use Shieldfy\Cache\CacheInterface;

class FileDriver implements CacheInterface
{
    private $path;
    private $timeout = 3600;

    public function __construct($config = [], $timeout = '')
    {
        $this->path = $config['path'];
        if ($timeout) {
            $this->timeout = $timeout;
        }
    }

    public function has($key)
    {
        $filename = $this->path.$key.'.json';
        if (file_exists($filename)) {
            if ((filemtime($filename) + $this->timeout) > time()) {
                return true;
            } 
            unlink($filename);
        }

        return false;
    }

    public function set($key, $value)
    {
        $filename = $this->path.$key.'.json';
        file_put_contents($filename, json_encode($value));
    }

    public function get($key)
    {
        if (!$this->has($key)) {
            return false;
        }
        $filename = $this->path.$key.'.json';
        return json_decode(file_get_contents($filename), 1);
    }
}
