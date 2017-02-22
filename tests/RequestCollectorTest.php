<?php
namespace Shieldfy\Test;
use PHPUnit\Framework\TestCase;
use Shieldfy\Collectors\RequestCollector;
class RequestCollectorTest extends TestCase
{
    protected $request;
    protected $server;
    protected $get;
    protected $post;
    protected $created;
    public function setup()
    {
        $this->server = [
            'REQUEST_METHOD' => 'POST',
            'PHP_SELF'       => '/index.php',
            'PATH_INFO'      => '/hi/',
            'REQUEST_URI'    => '/?x=1',
            'HTTP_ORIGIN'    => 'example.com',
            'HTTP_HOST'      => 'example.com',
            'HTTP_REFERER'   => 'https://facebook.com',
        ];
        $this->get = [
            'x'=> 1,
        ];
        $this->post = [
            'name'   => 'hello',
            'contact'=> [
                'address'=> 'some street',
                'tel'    => '111 111 111',
            ],
        ];
        $this->created = time();
        $this->request = new RequestCollector($this->get, $this->post, $this->server);
    }
    public function testGetInfo()
    {
        $info = $this->request->getInfo();
        $this->assertLessThanOrEqual($info['created'], $this->created);
        $this->assertEquals($info['method'], $this->server['REQUEST_METHOD']);
        $this->assertEquals($info['get'], $this->get);
        $this->assertEquals($info['post'], $this->post);
        $this->assertEquals($info['server'], $this->server);
    }

    public function testScore()
    {
        $this->request->setScore(50);
        $this->assertEquals($this->request->getScore(),50);
        $info = $this->request->getInfo();
        $this->assertEquals($info['score'],50);
    }
}