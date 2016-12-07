<?php

namespace Shieldfy\Test;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

use Shieldfy\Config;
use Shieldfy\Cache;
use Shieldfy\Event;
use Shieldfy\Exceptions\ExceptionHandler;
use Shieldfy\User;
use Shieldfy\Request;
use Shieldfy\Session;

class SessionTest extends TestCase
{
    protected $root;
    protected $cache;
    protected $sampleSessionId;
    protected $sampleUserScore;
    protected $event;

    public function setup()
    {
    	$this->root = vfsStream::setup();
        mkdir($this->root->url().'/tmp/', 0700, true);
        mkdir($this->root->url().'/data/', 0700, true);

        $config = New Config;
        $config['debug'] = true;
        $this->exceptionHandler = new ExceptionHandler($config);

        $this->cache = (new Cache($this->exceptionHandler))->setDriver('file', [
            'path'=> $this->root->url().'/tmp/',
        ]);

        $this->sampleSessionId = 'abcdefgh';
        $this->sampleUserScore = 5; //clean

        // mock api class	
        $this->event = $this->createMock(Event::class);

        $this->exampleData = json_encode([
            'status'   => 'success',
            'sessionId'=> $this->sampleSessionId,
            'score'    => $this->sampleUserScore
        ]);
        $this->event->method('trigger')
             ->willReturn(json_decode($this->exampleData));
    }


    public function testLoadNewUser()
    {
    	$request = new Request([],[],[
    		'REQUEST_METHOD'=>'get'
    	]);
    	$user = new User($request);
    	$session = new Session($user,$request,$this->event,$this->cache);

    	$this->assertTrue($session->isFirstVisit());

    	$session->save();

    	$this->assertTrue($this->root->hasChild('tmp/'.$user->getId().'.json'));

    	$userInfo = json_decode($this->root->getChild('tmp/'.$user->getId().'.json')->getContent(),1);

    	$expectedUserInfo = $user->getInfo();
    	$expectedUserInfo['sessionId'] = $this->sampleSessionId;
    	$expectedUserInfo['score'] = $this->sampleUserScore;

    	$this->assertEquals($expectedUserInfo , $userInfo);

        $this->assertTrue($this->root->hasChild('tmp/'.$this->sampleSessionId.'.json'));

        $savedRequestInfo = json_decode($this->root->getChild('tmp/'.$this->sampleSessionId.'.json')->getContent(),1);
        $expectedRequest = [$request->getInfo()];

        $this->assertEquals($expectedRequest, $savedRequestInfo);

        //test save after sync
        $session->save(false);
        $savedRequestInfo = json_decode($this->root->getChild('tmp/'.$this->sampleSessionId.'.json')->getContent(),1);

        $expectedRequest = $request->getInfo();
        $expectedRequest['info']['params']['get'] = [];
        $expectedRequest['info']['params']['post'] = [];
        $expectedRequest = [$expectedRequest];

        $this->assertEquals($expectedRequest, $savedRequestInfo);
    }

    public function testLoadExistUser()
    {
    	$ip = '127.0.0.1';
    	$request = new Request([],[],[
    		'REQUEST_METHOD'=>'get',
    		'REMOTE_ADDR'=>$ip
    	]);
    	$user = new User($request);

    	$oldUserInfo = [
            'id'       => ip2long($ip),
            'ip'       => $ip,
            'userAgent'=> 'Mozilla',
            'sessionId'=> 'defghij',
            'score'    => 3,
        ];

        //set fake visit
        file_put_contents($this->root->url().'/tmp/'.ip2long($ip).'.json', json_encode($oldUserInfo));
        $sampleHistory = '[{"created":1479298788,"info":{"method":"GET","params":{"get":[],"post":[],"server":{"ps":"\/index.php","uri":"\/","hh":"example.com"}}}}]';
        file_put_contents($this->root->url().'/tmp/'.$oldUserInfo['sessionId'].'.json', $sampleHistory);

        $session = new Session($user,$request,$this->event,$this->cache);
        $this->assertFalse($session->isFirstVisit());

        $info = $session->getInfo();

        $this->assertEquals('defghij', $info['sessionId']);
        $userInfo = $info['user']->getInfo();
        $this->assertEquals(3, $userInfo['score']);
        $this->assertEquals(json_decode($sampleHistory, 1), $info['history']);

    }

}