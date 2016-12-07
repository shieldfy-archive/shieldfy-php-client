<?php

namespace Shieldfy\Test;

use PHPUnit\Framework\TestCase;
use Shieldfy\Request;
use Shieldfy\User;

class UserTest extends TestCase
{

    public function testUserInfoNoIp()
    {

        $request = new Request([],[],['REQUEST_METHOD'=>'get']);
        $user = new User($request);
        $info = $user->getInfo();
        $this->assertEquals('0.0.0.0', $info['ip']);
        $this->assertEquals(ip2long('0.0.0.0'), $info['id']);
    }

    public function testUserGetID()
    {
        $request = new Request([],[],['REQUEST_METHOD'=>'get']);
        $user = new User($request);
        $this->assertEquals( ip2long('0.0.0.0'), $user->getId());
    }

    public function testUserInfoThroughProxy1()
    {
        $request = new Request([],[],[
            'REQUEST_METHOD'=>'get',
            'REMOTE_ADDR'=>'55.44.33.22', //proxy ip
            'HTTP_X_FORWARDED_FOR' => '114.113.112.111,113.112.111.110'
        ]);
        $user = new User($request);
        $info = $user->getInfo();
        $this->assertEquals('114.113.112.111', $info['ip']);
        $this->assertEquals(ip2long('114.113.112.111'), $info['id']);
    }

    public function testUserInfoThroughProxy2()
    {
        $request = new Request([],[],[
            'REQUEST_METHOD'=>'get',
            'REMOTE_ADDR'=>'55.44.33.22', //proxy ip
            'HTTP_CLIENT_IP' => '114.113.112.111'
        ]);
        $user = new User($request);

        $info = $user->getInfo();
        $this->assertEquals('114.113.112.111', $info['ip']);
        $this->assertEquals(ip2long('114.113.112.111'), $info['id']);
    }

    public function testUserInfoThroughProxy3()
    {
        $request = new Request([],[],[
            'REQUEST_METHOD'=>'get',
            'REMOTE_ADDR'=>'55.44.33.22', //proxy ip
            'HTTP_X_REAL_IP' => '114.113.112.111'
        ]);
        $user = new User($request);

        $info = $user->getInfo();
        $this->assertEquals('114.113.112.111', $info['ip']);
        $this->assertEquals(ip2long('114.113.112.111'), $info['id']);
    }

    public function testUserInfoDirectAccess()
    {
        $request = new Request([],[],[
            'REQUEST_METHOD'=>'get',
            'REMOTE_ADDR'=>'114.113.112.111', //proxy ip
        ]);
        $user = new User($request);
        $info = $user->getInfo();
        $this->assertEquals('114.113.112.111', $info['ip']);
        $this->assertEquals(ip2long('114.113.112.111'), $info['id']);
    }

    public function testUserInfoUserAgent()
    {
        $request = new Request([],[],[
            'REQUEST_METHOD'=>'get',
            'HTTP_USER_AGENT'=>'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1', //proxy ip
        ]);
        $user = new User($request);
        $info = $user->getInfo();
        $this->assertEquals($info['userAgent'], 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1');
    }

    public function testOutsiteInfo()
    {
        $request = new Request([],[],[
            'REQUEST_METHOD'=>'get',
            'HTTP_USER_AGENT'=>'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1', //proxy ip
        ]);
        $user = new User($request);
        $user->setSessionId('123456');
        $this->assertEquals('123456', $user->getSessionId());

        $user->setScore(3);
        $this->assertEquals(3, $user->getScore());

        $info = $user->getInfo();
        $this->assertEquals('123456', $info['sessionId']);
        $this->assertEquals(3,$info['score']);

    }
}
