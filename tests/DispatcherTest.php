<?php

namespace Shieldfy\Test;

use PHPUnit\Framework\TestCase;

use Shieldfy\Http\Dispatcher;
use Shieldfy\Http\ApiClient;
use Shieldfy\Config;

class DispatcherTest extends TestCase
{
    protected $config;

    public $api;

    public function setup()
    {
        $this->config = new Config();
        $this->config['app_key'] = 'testKey';
        $this->config['app_secret'] = 'testSecret';
        $this->config['endpoint'] = 'https://shieldfy.io';

        $this->api = $this->getMockBuilder(ApiClient::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $this->api->method('request')
                ->will($this->returnCallback(function ($event, $data) {
                    return [$event,$data];
                }));
    }

    public function testTriggerEvent()
    {
        $dispatcher = new Dispatcher($this->config, $this->api);
        $res = $dispatcher->trigger('session/start', ['user'=>'someuser']);
        $this->assertEquals(['/session/start','{"user":"someuser"}'], $res);
    }

    public function testFlush()
    {
        $dispatcher = new Dispatcher($this->config, $this->api);
        $dispatcher->setData(['user'=>'anotheruser']);
        $this->assertEquals(1, $dispatcher->hasData());
        $this->assertEquals(['user'=>'anotheruser'], $dispatcher->getData());
        $res = $dispatcher->flush();
        $this->assertEquals(['/session/threat','{"user":"anotheruser"}'], $res);
    }
}
