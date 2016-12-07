<?php

namespace Shieldfy\Test;

use PHPUnit\Framework\TestCase;
use Shieldfy\Exceptions\EventNotExistsException;
use Shieldfy\Exceptions\ExceptionHandler;
use Shieldfy\Config;
use Shieldfy\ApiClient;
use Shieldfy\Event;

class EventsTest extends TestCase
{
	protected $config;
	protected $exceptionHandler;
	protected $event;
	protected $api;
	protected $exampleData;

	public function setup()
	{
		$this->config = new Config;
		$this->config['debug'] = true;
		$this->exceptionHandler = new ExceptionHandler($this->config);
		
		// mock event class	
        $api = $this->createMock(ApiClient::class);

        $this->exampleData = json_encode([
            'status'=> 'success'
        ]);
        $api->method('request')
             ->willReturn(json_decode($this->exampleData));

        $this->api = $api;

		$this->event = new Event($this->api,$this->exceptionHandler);
	}

	public function testEventNotFound()
	{	
		$this->expectException(EventNotExistsException::class);	
		$this->event->trigger('notExistsEvent',[]);		
	}

	public function testEventTrigger()
	{
		$res = $this->event->trigger('install',[]);
		$this->assertEquals(json_decode($this->exampleData),$res);
	}
}