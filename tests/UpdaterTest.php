<?php
namespace Shieldfy\Test;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use Shieldfy\Updater;
use Shieldfy\Config;
use Shieldfy\Collectors\RequestCollector;

class UpdaterTest extends TestCase
{
    public function testUpdateProcess()
    {

        //set virtual filesystem
        $root = vfsStream::setup();
        mkdir($root->url().'/data/', 0700, true);


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


        $update = $this->getMockBuilder(Updater::class);
        $update = $update->setConstructorArgs([$config])
                ->setMethods(['trigger'])
                ->getMock();

        $update->method('trigger')
                ->willReturn(json_decode($exampleData));

        $res = $update->run();
        $this->assertTrue($res);

        //check the result
        $this->assertTrue($root->hasChild('data/installed'));
        $this->assertTrue($root->hasChild('data/updated'));
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
