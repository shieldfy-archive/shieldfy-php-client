<?php
namespace Shieldfy;
use Shieldfy\Exceptions\ExceptionHandler;
use Shieldfy\Exceptions\FailedExtentionLoadingException;

/**
 * Caching class
 */

class Cache
{

/**
 * @const int SESSION_TIMEOUT
 *
 * @var NULL $driver Driver class
 * @var string[] $config Contains dirver class config
 * @var string[] $drivers Supported driver types
 */
    const SESSION_TIMEOUT = 3600; // one hour

    private static $driver = NULL;
    private static $config = [];
    private static $drivers = [
        'file' => \Shieldfy\Cache\Drivers\FileCacheDriver::class,
        'memcached' => \Shieldfy\Cache\Drivers\MemcachedDriver::class,
    ];

    /**
     * Sets the caching driver.
     *
     * @param string $driver_type
     * @param mixed[] $config
     * @return object $driver
     */

    public static function setDriver($driver_type, $config = []) {
        if(!isset(self::$drivers[$driver_type])){
            
            ExceptionHandler::throwException(new FailedExtentionLoadingException('Caching driver not found or supported.'));
            return; //return to avoid extra execution if errors is off
        }

        $driverClass = self::$drivers[$driver_type];
        self::$driver = new $driverClass($config, self::SESSION_TIMEOUT);
    }

    /**
     * Gets current driver instance.
     *
     * @return $driver
     */

    public static function getInstance(){
        return self::$driver;
    }

}
