<?php
namespace Shieldfy\Test;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;

use Shieldfy\Shieldfy;
use Shieldfy\Install;
use Shieldfy\Event;
use Shieldfy\ApiClient;
class InstallTest extends TestCase
{
	protected $root;
	protected $api;

	public function setup()
    {
    	//set virtual filesystem
        $this->root = vfsStream::setup();
        mkdir($this->root->url().'/data/',0700,true);

        //set default data
        $_SERVER['HTTP_HOST'] = 'unittest';
        $_SERVER['SERVER_ADDR'] = '127.0.0.1';
        $_SERVER['SERVER_SOFTWARE'] = '';
        $_SERVER['SERVER_PORT'] = '';

        // mock event class 
        $api = $this->createMock(ApiClient::class);
        $exampleInstallData = json_encode([
        	'status'=>'success',
        	'data'=>[
        		'site_rules' => ["site_role_1"],
        		'pre_rules' => ["pre_rules_1"],
        		'hard_rules' => ["hard_rules_1"],
        		'soft_rules' => ["soft_rules_1"]
        	]
        ]);
        $api->method('request')
             ->willReturn(json_decode($exampleInstallData));
        $this->api = $api;
    }
    

	public function testInstallProcess()
	{
		// set the virtual dir 
		Shieldfy::setRootDir($this->root->url());
		//point the apiclient to the mocked one
		Event::$apiClient = $this->api;

		Install::init();

		//check the result 
		$this->assertTrue($this->root->hasChild('data/installed'));
		$this->assertTrue($this->root->hasChild('data/site_rules'));
		$this->assertTrue($this->root->hasChild('data/pre_rules'));
		$this->assertTrue($this->root->hasChild('data/hard_rules'));
		$this->assertTrue($this->root->hasChild('data/soft_rules'));

		$this->assertEquals($this->root->getChild('data/site_rules')->getContent(),'["site_role_1"]');
		$this->assertEquals($this->root->getChild('data/pre_rules')->getContent(),'["pre_rules_1"]');
		$this->assertEquals($this->root->getChild('data/hard_rules')->getContent(),'["hard_rules_1"]');
		$this->assertEquals($this->root->getChild('data/soft_rules')->getContent(),'["soft_rules_1"]');
	}
}