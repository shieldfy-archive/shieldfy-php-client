<?php

namespace Shieldfy\Test;

use PHPUnit\Framework\TestCase;
use Shieldfy\ApiClient;
use Shieldfy\Config;
use Shieldfy\Exceptions\ExceptionHandler;

class ApiClientTest extends TestCase
{
    protected $config;
    protected $exceptionsHandler;
    protected $api;

    public function setup()
    {
        $this->config = new Config();
        $this->config['app_key'] = 'testKey';
        $this->config['app_secret'] = 'testSecret';
        $this->config['apiEndpoint'] = 'https://shieldfy.io';
        $this->exceptionsHandler = new ExceptionHandler($this->config);
        $this->api = new ApiClient($this->config, $this->exceptionsHandler);
    }

    public function testAuthenticationHeaders()
    {
        $calculatedHash = $this->invokeMethod($this->api, 'calculateBodyHash', [json_encode(['somebody'])]);

        $hash = 'ae87b779570ffee163c412368a9574b2287aa900d48fa25b26236fd8f6518b46'; // hmac for json_encode(array('somebody'))

        $this->assertEquals($hash, $calculatedHash);
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
