<?php
namespace Shieldfy\Test;

use PHPUnit\Framework\TestCase;
use Shieldfy\User;

class UserTest extends TestCase
{
	public function testUserInfoNoIp()
	{
		unset($_SERVER['REMOTE_ADDR']);
		unset($_SERVER['HTTP_X_FORWARDED_FOR']);
		unset($_SERVER['HTTP_CLIENT_IP']);
		unset($_SERVER['HTTP_X_REAL_IP']);
		$user = new User();
		$info = $user->getInfo();
		$this->assertEquals($info['ip'],'0.0.0.0');
		$this->assertEquals($info['id'],ip2long('0.0.0.0'));
	}
	public function testUserInfoThroughProxy1()
	{
		$_SERVER['REMOTE_ADDR'] = '55.44.33.22'; //proxy ip
		$_SERVER['HTTP_X_FORWARDED_FOR'] = '114.113.112.111,113.112.111.110';
		$user = new User();
		$info = $user->getInfo();
		$this->assertEquals($info['ip'],'114.113.112.111');
		$this->assertEquals($info['id'],ip2long('114.113.112.111'));
	}
	public function testUserInfoThroughProxy2()
	{
		$_SERVER['REMOTE_ADDR'] = '55.44.33.22'; //proxy ip
		$_SERVER['HTTP_CLIENT_IP'] = '114.113.112.111';
		$user = new User();
		$info = $user->getInfo();
		$this->assertEquals($info['ip'],'114.113.112.111');
		$this->assertEquals($info['id'],ip2long('114.113.112.111'));
	}
	public function testUserInfoThroughProxy3()
	{
		$_SERVER['REMOTE_ADDR'] = '55.44.33.22'; //proxy ip
		$_SERVER['HTTP_X_REAL_IP'] = '114.113.112.111';
		$user = new User();
		$info = $user->getInfo();
		$this->assertEquals($info['ip'],'114.113.112.111');
		$this->assertEquals($info['id'],ip2long('114.113.112.111'));
	}
	public function testUserInfoDirectAccess()
	{
		$_SERVER['REMOTE_ADDR'] = '114.113.112.111';
		$user = new User();
		$info = $user->getInfo();
		$this->assertEquals($info['ip'],'114.113.112.111');
		$this->assertEquals($info['id'],ip2long('114.113.112.111'));
	}
	public function testUserInfoUserAgent()
	{
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1';
		$user = new User();
		$info = $user->getInfo();
		$this->assertEquals($info['userAgent'],'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1');
	}
}