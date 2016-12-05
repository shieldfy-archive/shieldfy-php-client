<?php
namespace Shieldfy\Cache\Drivers;
use Shieldfy\Cache\CacheInterface;
use Shieldfy\Exceptions\ExceptionHandler;
use Shieldfy\Exceptions\FailedExtentionLoadingException;
class MemcachedDriver implements CacheInterface
{
    private $adapter = null;
    private $timeout = 3600;

    public function __construct($config = [],$timeout = ''){
        if (!extension_loaded('memcached')) {
            ExceptionHandler::throwException(new FailedExtentionLoadingException('Memcached extension failed to load.'));
        }
        if($timeout) $this->timeout = $timeout;
        $this->adapter = new \Memcached();
        $this->adapter->setOption(\Memcached::OPT_COMPRESSION, false);
        $this->adapter->addServers($config['servers']);
    }
    public function has($key){
        return $this->adapter->get($key);
    }
    public function set($key,$value){
        return $this->adapter->set($key, $value,time() + $this->timeout);
    }
    public function get($key){
        return $this->adapter->get($key);
    }
}
