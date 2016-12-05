<?php
namespace Shieldfy\Test;

use PHPUnit\Framework\TestCase;
use Shieldfy\Request;

class RequestTest extends TestCase
{
	protected $request;
	protected $server;
	protected $get;
	protected $post;
	public function setup()
	{
		$this->server = $_SERVER = [
			'REQUEST_METHOD' => 'POST',
			'PHP_SELF' => '/index.php',
			'PATH_INFO' => '/hi/',
			'REQUEST_URI' => '/?x=1',
			'HTTP_ORIGIN' => 'example.com',
			'HTTP_HOST' => 'example.com',
			'HTTP_REFERER' => 'https://facebook.com'
		];
		$this->get = $_GET = [
			'x'=>1
		];
		$this->post = $_POST = [
			'name'=>'hello',
			'contact'=> [
				'address'=>'some street',
				'tel' => '111 111 111'
			]
		];
		$this->request = new Request();
	}
	
	public function testGetInfo()
	{
		$info = $this->request->getInfo();
		$this->assertEquals($info['method'],$this->server['REQUEST_METHOD']);
		$this->assertEquals($info['params']['get'],$this->get);
		$this->assertEquals($info['params']['post'],$this->post);
		$this->assertEquals($info['params']['server']['ps'],$this->server['PHP_SELF']);
		$this->assertEquals($info['params']['server']['pi'],$this->server['PATH_INFO']);
		$this->assertEquals($info['params']['server']['uri'],$this->server['REQUEST_URI']);
		$this->assertEquals($info['params']['server']['ho'],$this->server['HTTP_ORIGIN']);
		$this->assertEquals($info['params']['server']['hh'],$this->server['HTTP_HOST']);
		$this->assertEquals($info['params']['server']['r'],$this->server['HTTP_REFERER']);
	}
	
}