<?php

namespace Shieldfy\Test;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Shieldfy\Cache;
use Shieldfy\Config;
use Shieldfy\Exceptions\CacheDriverNotExistsException;
use Shieldfy\Exceptions\ExceptionHandler;

class CacheTest extends TestCase
{
    protected $root;
    protected $exceptionHanlder;

    public function setUp()
    {
        //set virtual filesystem
        $this->root = vfsStream::setup();
        mkdir($this->root->url().'/tmp/', 0700, true);

        $config = new Config();
        $config['debug'] = true;
        $this->exceptionHandler = new ExceptionHandler($config);
    }

    public function testNotExistDriverException()
    {
        $this->expectException(CacheDriverNotExistsException::class);
        $cache = new Cache($this->exceptionHandler);
        $cache->setDriver('notExists');
    }

    public function testInstanceCreationAndRetrival()
    {
        $cache = (new Cache($this->exceptionHandler))->setDriver('file', [
            'path'=> $this->root->url().'/tmp/',
        ]);

        $this->assertInternalType('object', $cache);
    }

    public function testSet()
    {
        $cache = (new Cache($this->exceptionHandler))->setDriver('file', [
            'path'=> $this->root->url().'/tmp/',
        ]);
        $cache->set('foo', ['bar']);
        $this->assertEquals('["bar"]', $this->root->getChild('tmp/foo.json')->getContent());
    }

    public function testGet()
    {
        $cache = (new Cache($this->exceptionHandler))->setDriver('file', [
            'path'=> $this->root->url().'/tmp/',
        ]);
        $cache->set('foo', ['bar']);
        $res = $cache->get('foo');

        $this->assertEquals($res, ['bar']);
        $this->assertFalse($cache->get('bar'));
    }

    public function testHas()
    {
        $cache = (new Cache($this->exceptionHandler))->setDriver('file', [
            'path'=> $this->root->url().'/tmp/',
        ]);
        $cache->set('foo', ['bar']);

        $this->assertTrue($cache->has('foo'));
        $this->assertFalse($cache->has('bar'));
    }
}
