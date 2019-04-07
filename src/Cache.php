<?php

namespace Shieldfy;

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
        'file' => \Shieldfy\Cache\Drivers\FileDriver::class,
    ];


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
            $driverType = 'null';
        }

        $driverClass = $this->drivers[$driverType];

        return new $driverClass($config, self::SESSION_TIMEOUT);
    }
}
