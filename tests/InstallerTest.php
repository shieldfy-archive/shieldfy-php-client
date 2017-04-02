<?php
namespace Shieldfy\Test;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use Shieldfy\Installer;
use Shieldfy\Config;
use Shieldfy\Collectors\RequestCollector;

class InstallerTest extends TestCase
{
    public function testInstallProcess()
    {

        //set virtual filesystem
        $root = vfsStream::setup();
        mkdir($root->url().'/data/', 0700, true);

        $server = [
            'REQUEST_METHOD' => 'POST',
            'HTTP_HOST'      => 'example.com',
            'REQUEST_URI'    => '/?x=1',
            'SERVER_PORT'    => 80
        ];
        $request = new RequestCollector([],[],$server);
        $config = new Config;
        $config['rootDir'] = $root->url();

        $exampleData = json_encode([
            'status'=> 'success',
            'data'  => [
                'upload' => ['["upload"]'],
                'request'  => ['["request"]'],
                'api' => ['["api"]'],
                'exceptions' => ['["exceptions"]'],
                'query' => ['["query"]'],
                'view' => ['["view"]']
            ]
        ]);


        $install = $this->getMockBuilder(Installer::class);
        $install = $install->setConstructorArgs([$request,$config])
                ->setMethods(['trigger'])
                ->getMock();

        $install->method('trigger')
                ->willReturn(json_decode($exampleData));

        $res = $install->run();
        $this->assertTrue($res);

        //check the result
        $this->assertTrue($root->hasChild('data/installed'));
        $this->assertTrue($root->hasChild('data/upload.json'));
        $this->assertTrue($root->hasChild('data/request.json'));
        $this->assertTrue($root->hasChild('data/api.json'));
        $this->assertTrue($root->hasChild('data/exceptions.json'));
        $this->assertTrue($root->hasChild('data/query.json'));
        $this->assertTrue($root->hasChild('data/view.json'));
        $this->assertEquals('["upload"]', $root->getChild('data/upload.json')->getContent());
        $this->assertEquals('["request"]', $root->getChild('data/request.json')->getContent());
        $this->assertEquals('["api"]', $root->getChild('data/api.json')->getContent());
        $this->assertEquals('["exceptions"]', $root->getChild('data/exceptions.json')->getContent());
        $this->assertEquals('["query"]', $root->getChild('data/query.json')->getContent());
        $this->assertEquals('["view"]', $root->getChild('data/view.json')->getContent());

    }
}
