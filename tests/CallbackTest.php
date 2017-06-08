<?php
namespace Shieldfy\Test;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use Shieldfy\Config;
use Shieldfy\Collectors\RequestCollector;
use Shieldfy\Cache\CacheManager;
use Shieldfy\Callbacks\CallbackHandler;
/**
 * @runTestsInSeparateProcesses
 */

class CallbackTest extends TestCase
{

    protected $root;
    protected $config;
    protected $defaultServerRequest;
    protected $cache;



    public function setup()
    {
        //set virtual filesystem
        $this->root = vfsStream::setup();
        mkdir($this->root->url().'/src/', 0700, true);
        mkdir($this->root->url().'/tmp/',0700,true);
        mkdir($this->root->url().'/log/',0700,true);
        $config = new Config();
        $config['rootDir'] = $this->root->url().'/src';
        $config['logsDir'] = $this->root->url().'/log';
        $config['app_key'] = 'anyKey';
        $config['app_secret'] = 'anySecret';
        $config['debug'] = true;
        $config['version'] = '2.1.0';
        $this->config = $config;

        $this->defaultServerRequest = [
            'REQUEST_METHOD' => 'GET',
            'HTTP_HOST'      => 'example.com',
            'SERVER_PROTOCOL'=> 'HTTP/1.1',
            'REQUEST_URI'    => '/',
            'SERVER_PORT'    => 80
        ];

        $path = $this->root->url().'/tmp/';
        $this->cache = (new CacheManager($this->config))->setDriver('file', [
            'path'=> $path,
        ]);
    }


    public function testHealthCheckCallback()
    {
        $this->expectOutputString('{"status":"success","version":"2.1.0"}');
        $server = array_merge($this->defaultServerRequest,[
            'HTTP_X_SHIELDFY_CALLBACK' => 'health',
            'HTTP_X_SHIELDFY_CALLBACK_TOKEN'=>hash_hmac('sha256', $this->config['app_key'], $this->config['app_secret'])
        ]);
        $requestCollector = new RequestCollector([], [], $server, [], []);
        
        (new CallbackHandler($requestCollector, $this->config, $this->cache))->catchCallback();
    }

    public function testLogsCallback()
    {
        $this->expectOutputString('{"20170321.log":"0-Argument 3 passed to MonitorBase::__construct() must be an instance of Session, instance of Session given, called in MonitorsBag.php on line 56-MonitorBase.php-39","20170326.log":"0-Argument 3 passed to MonitorBase::__construct() must be an instance of Session, instance of Session given, called in MonitorsBag.php on line 57-MonitorBase.php-40"}');

        file_put_contents($this->root->url().'/log/20170321.log',"0-Argument 3 passed to MonitorBase::__construct() must be an instance of Session, instance of Session given, called in MonitorsBag.php on line 56-MonitorBase.php-39");
        file_put_contents($this->root->url().'/log/20170326.log',"0-Argument 3 passed to MonitorBase::__construct() must be an instance of Session, instance of Session given, called in MonitorsBag.php on line 57-MonitorBase.php-40");

        $server = array_merge($this->defaultServerRequest,[
            'HTTP_X_SHIELDFY_CALLBACK' => 'logs',
            'HTTP_X_SHIELDFY_CALLBACK_TOKEN'=>hash_hmac('sha256', $this->config['app_key'], $this->config['app_secret'])
        ]);
        $requestCollector = new RequestCollector([], [], $server, [], []);
        
        (new CallbackHandler($requestCollector, $this->config, $this->cache))->catchCallback();
    }
    
}