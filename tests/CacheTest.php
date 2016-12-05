<?php

namespace Shieldfy\Test;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Shieldfy\Cache;

class CacheTest extends TestCase
{
    protected $root;

    public function setUp()
    {
        //set virtual filesystem
        $this->root = vfsStream::setup();
        mkdir($this->root->url().'/tmp/', 0700, true);
    }

    public function testInstanceCreationAndRetrival()
    {
        Cache::setDriver('file', [
            'path'=> $this->root->url().'/tmp/',
        ]);
        $this->assertInternalType('object', Cache::getInstance());
    }

    public function testSet()
    {
        Cache::setDriver('file', [
            'path'=> $this->root->url().'/tmp/',
        ]);
        $cache = Cache::getInstance();
        $cache->set('foo', ['bar']);
        $this->assertEquals($this->root->getChild('tmp/foo.json')->getContent(), '["bar"]');
    }

    public function testGet()
    {
        Cache::setDriver('file', [
            'path'=> $this->root->url().'/tmp/',
        ]);
        $cache = Cache::getInstance();
        $cache->set('foo', ['bar']);
        $res = $cache->get('foo');

        $this->assertEquals($res, ['bar']);
        $this->assertFalse($cache->get('bar'));
    }

    public function testHas()
    {
        Cache::setDriver('file', [
            'path'=> $this->root->url().'/tmp/',
        ]);
        $cache = Cache::getInstance();
        $cache->set('foo', ['bar']);

        $this->assertTrue($cache->has('foo'));
        $this->assertFalse($cache->has('bar'));
    }
}
