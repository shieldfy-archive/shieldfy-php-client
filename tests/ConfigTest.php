<?php
namespace Shieldfy\Test;
use PHPUnit\Framework\TestCase;

use Shieldfy\Config;

class ConfigTest extends TestCase
{
    public function testLoadDefaults()
    {
        $config = new Config(['defaultKey'=>'defaultValue']);
        $this->assertEquals($config['defaultKey'], 'defaultValue');
    }
    public function testOverrideDefaults()
    {
        $config = new Config(['defaultKey'=>'defaultValue'], ['defaultKey'=>'anotherValue']);
        $this->assertEquals('anotherValue', $config['defaultKey']);
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