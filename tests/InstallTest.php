<?php

namespace Shieldfy\Test;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

use Shieldfy\Config;
use Shieldfy\ApiClient;
use Shieldfy\Event;
use Shieldfy\Exceptions\ExceptionHandler;
use Shieldfy\Request;
use Shieldfy\Install;

class InstallTest extends TestCase
{
    protected $root;
    protected $config;
    protected $api;
    protected $event;
    protected $exceptionHandler;

    public function setup()
    {
        //set virtual filesystem
        $this->root = vfsStream::setup();
        mkdir($this->root->url().'/data/', 0700, true);

        $this->config = new Config;
        $this->config['rootDir'] = $this->root->url();

        $this->exceptionHandler = new ExceptionHandler($this->config);
        
        // mock event class 
        $api = $this->createMock(ApiClient::class);

        $this->exampleData = json_encode([
            'status'=> 'success',
            'data'  => [
                'site_rules' => ['site_role_1'],
                'pre_rules'  => ['pre_rules_1'],
                'hard_rules' => ['hard_rules_1'],
                'soft_rules' => ['soft_rules_1'],
            ]
        ]);
        $api->method('request')
             ->willReturn(json_decode($this->exampleData));

        $this->api = $api;
        $this->event = new Event($this->api,$this->exceptionHandler);

        //set default data
        $this->request = new Request([],[],[
            'REQUEST_METHOD' => 'GET',
            'HTTP_HOST' => 'unittest',
            'SERVER_ADDR'=> '127.0.0.1',
            'SERVER_SOFTWARE' => '',
            'SERVER_PORT' => ''
        ]);

    }

    public function testAlreadyInstalled()
    {
        file_put_contents($this->root->url().'/data/installed', 1);
        $install = new Install($this->config,$this->request,$this->event, $this->exceptionHandler);
        $res = $install->run();
        $this->assertFalse($res);
        unlink($this->root->url().'/data/installed');
    }

    public function testInstallProcess()
    {

        $install = new Install($this->config,$this->request,$this->event, $this->exceptionHandler);
        $res = $install->run();

        $this->assertTrue($res);

        //check the result
        $this->assertTrue($this->root->hasChild('data/installed'));
        $this->assertTrue($this->root->hasChild('data/site_rules'));
        $this->assertTrue($this->root->hasChild('data/pre_rules'));
        $this->assertTrue($this->root->hasChild('data/hard_rules'));
        $this->assertTrue($this->root->hasChild('data/soft_rules'));

        $this->assertEquals('["site_role_1"]', $this->root->getChild('data/site_rules')->getContent());
        $this->assertEquals('["pre_rules_1"]', $this->root->getChild('data/pre_rules')->getContent());
        $this->assertEquals('["hard_rules_1"]', $this->root->getChild('data/hard_rules')->getContent());
        $this->assertEquals('["soft_rules_1"]', $this->root->getChild('data/soft_rules')->getContent());
    }
}
