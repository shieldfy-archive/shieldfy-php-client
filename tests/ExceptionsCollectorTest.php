<?php
namespace Shieldfy\Test;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Shieldfy\Config;
use Shieldfy\Collectors\ExceptionsCollector;
use Shieldfy\Http\Dispatcher;
use Shieldfy\Http\ApiClient;
use Exception;
use ErrorException;

class ExceptionsCollectorTest extends TestCase
{
    protected $root;
    protected $config;
    public $callbackCheckValue = 1;
    public $api;
    public $dispatcher;
    public function setUp()
    {
        //set virtual filesystem
        $this->root = vfsStream::setup();
        mkdir($this->root->url().'/log/', 0700, true);
        mkdir($this->root->url().'/src/', 0700, true);
        $config = new Config();
        //$config['debug'] = true;
        $config['rootDir'] = $this->root->url().'/src/';
        $this->config = $config;

        $this->api = $this->getMockBuilder(ApiClient::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $this->api->method('request')
                ->will($this->returnCallback(function ($event, $data) {
                    return [$event,$data];
                }));
        $this->dispatcher = new Dispatcher($this->config, $this->api);
    }

    public function testHandleErrors()
    {
        $exceptions = new ExceptionsCollector($this->config, $this->dispatcher);
        $exceptions->listen(function () {
            $this->assertTrue(true);
        });
        if (!class_exists(PHPUnit\Framework\Error\Error::class)) {
            $this->assertTrue(true);
            return;
        }
        $this->expectException(Error::class);
        $exceptions->handleErrors(1, 'h', 'h.php', 2, []);
    }

    public function testHandleExceptions()
    {
        $exceptions = new ExceptionsCollector($this->config, $this->dispatcher);
        $exceptions->listen(function () {
            $this->assertTrue(true);
        });
        $customException = new Exception('Hello');
        $this->expectException(Exception::class);
        $exceptions->handleExceptions($customException);
    }

    public function testInternalErrorLog()
    {
        $exceptions = new ExceptionsCollector($this->config, $this->dispatcher);
        $exceptions->listen(function () {
        });
        if (!class_exists(PHPUnit\Framework\Error\Error::class)) {
            $this->assertTrue(true);
            return;
        }
        $this->expectException(Error::class);
        $exceptions->handleErrors(1, 'h', $this->root->url().'/src/index.php', 2, []);
    }
}
