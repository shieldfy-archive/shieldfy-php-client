<?php
namespace Shieldfy\Test;

use PHPUnit\Framework\TestCase;

use Shieldfy\Config;

class ConfigTest extends TestCase
{
    public function testLoadDefaults()
    {
        $config = new Config([]);
        $this->assertEquals('',$config['api_key']);
        $this->assertEquals('',$config['api_secret']);
        $this->assertEquals(false,$config['debug']);
    }

    

    public function testOverrideDefaults()
    {
        $config = new Config(['debug'=>true]);
        $this->assertEquals(true, $config['debug']);
    }
    public function testSetGet()
    {
        $config = new Config();
        $config['someKey'] = 'someValue';
        $this->assertEquals('someValue', $config['someKey']);
        $this->assertTrue(isset($config['someKey']));
        unset($config['someKey']);
        $this->assertFalse(isset($config['someKey']));
    }
}
