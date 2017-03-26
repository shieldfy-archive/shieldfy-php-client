<?php
namespace Shieldfy\Cache;

use Shieldfy\Config;
use Shieldfy\Exceptions\CacheDriverNotExistsException;
use Shieldfy\Exceptions\Exceptionable;
use Shieldfy\Exceptions\Exceptioner;

/**
 * Caching class.
 */
class CacheManager implements Exceptionable
{
    use Exceptioner;

    /**
     * @const int SESSION_TIMEOUT
     */
    const SESSION_TIMEOUT = 3600; // one hour

    /**
     * @var string $config Contains dirver class config
     * @var string[] $drivers Supported driver types
     */
    private $config;
    private $drivers = [
        'file'      => \Shieldfy\Cache\Drivers\FileDriver::class,
        'memcached' => \Shieldfy\Cache\Drivers\MemcachedDriver::class,
        'null'      => \Shieldfy\Cache\Drivers\NullDriver::class,
    ];

    /**
     * Constructor.
     *
     * @param type $config
     *
     * @return type
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
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
            $this->throwException(new CacheDriverNotExistsException('Caching driver not found or supported.',301));
            $driverType = 'null';
        }
        $driverClass = $this->drivers[$driverType];
        return new $driverClass($config, self::SESSION_TIMEOUT);
    }
}
