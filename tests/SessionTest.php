<?php
namespace Shieldfy\Test;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use Shieldfy\Shieldfy;
use Shieldfy\Event;
use Shieldfy\ApiClient;
use Shieldfy\User;
use Shieldfy\Cache;
use Shieldfy\Request;
use Shieldfy\Session;
use Shieldfy\Install;

class SessionTest extends TestCase
{
	protected $root;
	protected $api;
	protected $sampleSessionId;
	protected $sampleUserScore;



	public function setup()
	{

		//set virtual filesystem
        $this->root = vfsStream::setup();
        mkdir($this->root->url().'/tmp/',0700,true);
        mkdir($this->root->url().'/data/',0700,true);
        Cache::setDriver('file', [
            'path'=>$this->root->url().'/tmp/'
        ]);
        $this->sampleSessionId = 'abcdefgh';
        $this->sampleUserScore = 5; //clean

        $exampleData = json_encode([
        	'status'=>'success',
        	'sessionID'=>$this->sampleSessionId,
        	'score'=>$this->sampleUserScore
        ]);
        //set mocking for the webservice
        $api = $this->createMock(ApiClient::class);
        $api->method('request')
             ->willReturn(json_decode($exampleData));
        $this->api = $api;

        Event::$apiClient = $this->api;
        	
        Shieldfy::setRootDir($this->root->url());
        //simulate rules in harddisk
        file_put_contents($this->root->url().'/data/pre_rules', "[]");
        
	}

	public function testLoadNewUser()
	{

		//capture the current user
        $user = new User();

        //load session details
        $session = Session::load($user);
        $userInfo = $session->user;

        $this->assertEquals($userInfo['sessionID'],$this->sampleSessionId);
        $this->assertEquals($userInfo['score'],$this->sampleUserScore);
        $this->assertEquals($session->sessionID,$this->sampleSessionId);
        $this->assertTrue($session->isFirstVisit());

        $request = new Request;
       	$session->request = [
                'created'=>time(),
                'info' => $request->getInfo()
        ];

        $session->analyze();
        //test
        $this->assertTrue($this->root->hasChild('tmp/'.$userInfo['id'].'.json'));
		$this->assertTrue($this->root->hasChild('tmp/'.$this->sampleSessionId.'.json'));
	}

	public function testLoadExistUser()
	{

		$_SERVER['REMOTE_ADDR'] = $ip = '8.8.8.8';
		$oldUserInfo = [
			'id'=>ip2long($ip),
			'ip'=>$ip,
			'userAgent'=>'Mozilla',
			'sessionID'=>'defghij',
			'score'=>3
		];
        //set fake visit
        file_put_contents($this->root->url().'/tmp/'.ip2long($ip).'.json', json_encode($oldUserInfo));
        $sampleHistory = '[{"created":1479298788,"info":{"method":"GET","params":{"get":[],"post":[],"server":{"ps":"\/index.php","uri":"\/","hh":"example.com"}}}}]';
        file_put_contents($this->root->url().'/tmp/'.$oldUserInfo['sessionID'].'.json', $sampleHistory);

        //now lets retrive the session

        //capture the current user
        $user = new User();
        //load session details
        $session = Session::load($user);
        $userInfo = $session->user;

        $this->assertFalse($session->isFirstVisit());
        $this->assertEquals($userInfo['sessionID'],'defghij');
        $this->assertEquals($userInfo['score'],3);
        $this->assertEquals($session->sessionID,'defghij');
        $this->assertEquals($session->history,json_decode($sampleHistory,1));

	}	
}