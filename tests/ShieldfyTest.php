<?php
namespace Shieldfy\Test;
use PHPUnit\Framework\TestCase;

use Shieldfy\Shieldfy;

class ShieldfyTest extends TestCase
{
	public function testConstants()
	{
		$this->assertEquals(Shieldfy::getApiVersion(),'0.1');
	}

	public function testDir()
	{
		Shieldfy::setRootDir('testDir/testSubDir');
		$this->assertEquals(Shieldfy::getRootDir(),'testDir/testSubDir');
	}

	public function testConfig()
	{
		$config = [
			'app_key'=>'testKey',
			'app_secret'=>'testSecret',
			'debug'=>true,
			'action'=>'listen',
			'disabledHeaders'=>['x-content-type-options']
		];
		Shieldfy::setConfig($config);

		$this->assertEquals(Shieldfy::getAppKeys(),[
				'app_key'=>'testKey',
				'app_secret'=>'testSecret'
			]);
		$this->assertEquals(Shieldfy::getConfig(),$config);
	}
}