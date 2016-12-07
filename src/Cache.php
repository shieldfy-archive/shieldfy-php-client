<?php

namespace Shieldfy;

use Shieldfy\Exceptions\ExceptionHandler;
use Shieldfy\Exceptions\CacheDriverNotExistsException;

/**
 * Caching class.
 */
class Cache
{
    /**
     * @const int SESSION_TIMEOUT
     */
    const SESSION_TIMEOUT = 3600; // one hour

    /**
     * @var null     Driver class
     * @var string[] $config Contains dirver class config
     * @var string[] $drivers Supported driver types
     */ 
    private $driver = null;
    private $config = [];
    private $drivers = [
        'file'      => \Shieldfy\Cache\Drivers\FileDriver::class,
        'memcached' => \Shieldfy\Cache\Drivers\MemcachedDriver::class,
        'null' => \Shieldfy\Cache\Drivers\NullDriver::class,
    ];

    /**
     * @var Object ExceptionHandler
     */ 
    protected $exceptionHandler;

    /**
     * Constructor
     * @param type $driverType 
     * @param type|array $config 
     * @return type
     */

    public function __construct(ExceptionHandler $exceptionHandler)
    {
        $this->exceptionHandler = $exceptionHandler;
    }

    /**
     * Sets the caching driver.
     *
     * @param string  $driver_type
     * @param mixed[] $config
     *
     * @return object $driver
     */
    public function setDriver($driverType, $config = [])
    {
        if (!isset($this->drivers[$driverType])) {
            $this->exceptionHandler->throwException(new CacheDriverNotExistsException('Caching driver not found or supported.'));
            $driverType = 'null';
        }

        $driverClass = $this->drivers[$driverType];
        return new $driverClass($config, self::SESSION_TIMEOUT);
    }

    
}
