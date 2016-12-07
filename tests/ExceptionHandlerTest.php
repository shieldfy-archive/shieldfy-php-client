<?php

namespace Shieldfy\Test;

use Exception;
use PHPUnit\Framework\TestCase;
use Shieldfy\Exceptions\ExceptionHandler;
use Shieldfy\Config;

class ExceptionHandlerTest extends TestCase
{

	/**
	 * @todo test for catching errors + mock event
	 */
	public function setup()
	{

	}

	public function testThrowException()
	{
		$config = new Config;
		$config['debug'] = true;

		$handler = new ExceptionHandler($config);
		$this->expectException(Exception::class);			
		$handler->throwException(new Exception("Test Exception"));
	}

	public function testNotThrowException()
	{
		$config = new Config;
		$config['debug'] = false;

		$handler = new ExceptionHandler($config);
		$handler->throwException(new Exception("Test Exception"));
		$this->assertTrue(true); //if it come here then the test passed and exeption not thrown
	}

}