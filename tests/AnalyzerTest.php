<?php

namespace Shieldfy\Test;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Shieldfy\Analyze\Analyzer;
use Shieldfy\Cache;
use Shieldfy\Shieldfy;

class AnalyzerTest extends TestCase
{
    public function setup()
    {
        //setup virtual rules
        $this->root = vfsStream::setup();
        mkdir($this->root->url().'/tmp/', 0700, true);

        Cache::setDriver('file', [
            'path'=> $this->root->url().'/tmp/',
        ]);

        mkdir($this->root->url().'/data/', 0700, true);
        $prerules = [
            2 => [
                '([^a-z0-9]+)', //just test rule not actual rule
            ],
        ];
        file_put_contents($this->root->url().'/data/pre_rules', json_encode($prerules));
        $softrules = [
            'x0' => [
                '([\.]{2}\/.*)', //just test rule not actual rule,
                '11',
            ],
            'x1' => [
                '(.*(etc|passwd|shadow))', //just test rule not actual rule,
                '15',
            ],
        ];
        file_put_contents($this->root->url().'/data/soft_rules', json_encode($softrules));
        Shieldfy::setRootDir($this->root->url());
    }

    public function testClean()
    {
        $data = [
            'user'=> [
                'ip'   => '8.8.8.8',
                'score'=> 0,
            ],
            'request'=> [
                'info'=> [
                    'method'=> 'get',
                    'params'=> [
                        'get'=> [
                            'x'=> 'xxxx!xxx',
                        ],
                        'post'=> [],
                    ],
                ],
            ],
        ];
        $analyzer = new Analyzer($data);
        $res = $analyzer->run();
        $this->assertEquals($res, 0);
    }

    public function testSuspicious()
    {
        $data = [
            'user'=> [
                'ip'   => '8.8.8.8',
                'score'=> 0,
            ],
            'request'=> [
                'info'=> [
                    'method'=> 'get',
                    'params'=> [
                        'get'=> [
                            'x'=> '../../',
                        ],
                        'post'=> [],
                    ],
                ],
            ],
        ];
        $analyzer = new Analyzer($data);
        $res = $analyzer->run();
        $result = $analyzer->getResult();

        $this->assertEquals($res, 2);
        $this->assertEquals($result['status'], 2);
        $this->assertEquals($result['total_score'], 64);
        $this->assertEquals($result['request']['get.x'], [
            'score'=> 16,
            'keys' => ['x0'],
        ]);
    }

    public function testDangerousUserSuspiciousActivity()
    {
        $data = [
            'user'=> [
                'ip'   => '8.8.8.8',
                'score'=> 10,
            ],
            'request'=> [
                'info'=> [
                    'method'=> 'get',
                    'params'=> [
                        'get'=> [
                            'x'=> '../../',
                        ],
                        'post'=> [],
                    ],
                ],
            ],
        ];
        $analyzer = new Analyzer($data);
        $res = $analyzer->run();
        $result = $analyzer->getResult();

        $this->assertEquals($res, 1);
        $this->assertEquals($result['status'], 1);
        $this->assertEquals($result['total_score'], 84);
        $this->assertEquals($result['request']['get.x'], [
            'score'=> 16,
            'keys' => ['x0'],
        ]);
    }

    public function testDanergous()
    {
        $data = [
            'user'=> [
                'ip'   => '8.8.8.8',
                'score'=> 10,
            ],
            'request'=> [
                'info'=> [
                    'method'=> 'get',
                    'params'=> [
                        'get'=> [
                            'x'=> '../../etc/passwd',
                        ],
                        'post'=> [],
                    ],
                ],
            ],
        ];
        $analyzer = new Analyzer($data);
        $res = $analyzer->run();
        $result = $analyzer->getResult();

        $this->assertEquals($res, 1);
        $this->assertEquals($result['status'], 1);
        $this->assertEquals($result['total_score'], 144);
        $this->assertEquals($result['request']['get.x'], [
            'score'=> 31,
            'keys' => ['x0', 'x1'],
        ]);
    }
}
