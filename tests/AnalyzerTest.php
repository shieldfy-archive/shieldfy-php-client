<?php

namespace Shieldfy\Test;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Shieldfy\Analyze\Analyzer;
use Shieldfy\Cache;
use Shieldfy\Config;
use Shieldfy\Event;
use Shieldfy\Exceptions\ExceptionHandler;
use Shieldfy\Request;
use Shieldfy\Session;
use Shieldfy\User;

class AnalyzerTest extends TestCase
{
    protected $config;
    protected $exceptionHandler;
    protected $cache;
    protected $event;

    public function setup()
    {
        //setup virtual files
        $root = vfsStream::setup();
        mkdir($root->url().'/tmp/', 0700, true);
        mkdir($root->url().'/data/', 0700, true);

        $prerules = [
            'p1' => [
                '([^a-z0-9]+)', //just test rule not actual rule
                '10',
            ],
        ];
        file_put_contents($root->url().'/data/pre_rules', json_encode($prerules));
        $softrules = [
            'x0' => [
                '([\.]{2}\/.*)', //just test rule not actual rule,
                '8',
            ],
            'x1' => [
                '(.*(etc|passwd|shadow))', //just test rule not actual rule,
                '15',
            ],
        ];
        file_put_contents($root->url().'/data/soft_rules', json_encode($softrules));

        $this->config = new Config();
        $this->config['rootDir'] = $root->url();

        $this->exceptionHandler = new ExceptionHandler($this->config);

        $this->cache = (new Cache($this->exceptionHandler))->setDriver('file', [
            'path'=> $root->url().'/tmp/',
        ]);

        // mock api class
        $this->event = $this->createMock(Event::class);
    }

    public function testClean()
    {
        $this->exampleData = json_encode([
            'status'   => 'success',
            'sessionId'=> 'abcdefgh',
            'score'    => 0,
        ]);
        $this->event->method('trigger')
             ->willReturn(json_decode($this->exampleData));

        $request = new Request([
            'x'=> 'xxxx!xxx',
        ], [], [
            'REQUEST_METHOD'=> 'GET',
        ]);
        $user = new User($request);
        $session = new Session($user, $request, $this->event, $this->cache);

        $analyzer = new Analyzer($session, $this->cache, $this->config);
        $analyzer->run();
        $result = $analyzer->getResult();
        $this->assertEquals(0, $result['status']);
    }

    public function testSuspicious()
    {
        $this->exampleData = json_encode([
            'status'   => 'success',
            'sessionId'=> 'abcdefgh',
            'score'    => 0,
        ]);
        $this->event->method('trigger')
             ->willReturn(json_decode($this->exampleData));

        $request = new Request([
            'x'=> '../../',
            'r'=> [
                'li'=> 5,
            ],
        ], [], [
            'REQUEST_METHOD'=> 'GET',
        ]);
        $user = new User($request);
        $session = new Session($user, $request, $this->event, $this->cache);

        $analyzer = new Analyzer($session, $this->cache, $this->config);
        $analyzer->run();
        $result = $analyzer->getResult();

        $this->assertEquals(2, $result['status']);
        $this->assertEquals(72, $result['total_score']);
        $this->assertEquals([
                'score'=> 18,
                'keys' => ['x0'],
            ],
            $result['request']['get.x']
        );
    }

    public function testDangerousUserSuspiciousActivity()
    {
        $this->exampleData = json_encode([
            'status'   => 'success',
            'sessionId'=> 'abcdefgh',
            'score'    => 10,
        ]);
        $this->event->method('trigger')
             ->willReturn(json_decode($this->exampleData));

        $request = new Request([
            'x'=> '../../',
            'r'=> [
                'li'=> 5,
            ],
        ], [], [
            'REQUEST_METHOD'=> 'GET',
        ]);
        $user = new User($request);
        $session = new Session($user, $request, $this->event, $this->cache);

        $analyzer = new Analyzer($session, $this->cache, $this->config);
        $analyzer->run();
        $result = $analyzer->getResult();

        $this->assertEquals(1, $result['status']);
        $this->assertEquals(92, $result['total_score']);
        $this->assertEquals([
                'score'=> 18,
                'keys' => ['x0'],
            ],
            $result['request']['get.x']
        );
    }

    public function testDanergous()
    {
        $this->exampleData = json_encode([
            'status'   => 'success',
            'sessionId'=> 'abcdefgh',
            'score'    => 0,
        ]);
        $this->event->method('trigger')
             ->willReturn(json_decode($this->exampleData));

        $request = new Request([
            'x'=> '../../etc/passwd',
            'r'=> [
                'li'=> 5,
            ],
        ], [], [
            'REQUEST_METHOD'=> 'GET',
        ]);
        $user = new User($request);
        $session = new Session($user, $request, $this->event, $this->cache);

        $analyzer = new Analyzer($session, $this->cache, $this->config);
        $analyzer->run();
        $result = $analyzer->getResult();

        $this->assertEquals(1, $result['status']);
        $this->assertEquals(132, $result['total_score']);
        $this->assertEquals([
                'score'=> 33,
                'keys' => ['x0', 'x1'],
            ],
            $result['request']['get.x']
        );
    }
}
