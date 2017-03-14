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
        $this->assertLessThanOrEqual($this->created,$info['created']);

        $this->assertEquals($this->server['REQUEST_METHOD'],$info['method']);
        $this->assertEquals($this->get,$this->request->get);

        $this->assertEquals(['get.x'=>1],$info['get']);
        $this->assertEquals([
                'get'=>['get.x'=>1],
                'created'=>$this->created,
                'score'=>null,
                'method'=>'POST',
            ],$this->request->getInfo('get'));

        $this->assertEquals($this->post,$this->request->post);
        $this->assertEquals([
                'post.name'=>'hello',
                'post.contact.address'=>'some street',
                'post.contact.tel'=>'111 111 111'
            ], $info['post']);
        $this->assertEquals([
                'post'=>[
                    'post.name'=>'hello',
                    'post.contact.address'=>'some street',
                    'post.contact.tel'=>'111 111 111'
                ],
                'created'=>$this->created,
                'score'=>null,
                'method'=>'POST'
            ], $this->request->getInfo('post'));

        $this->assertEquals($this->server,$this->request->server);
    }

    public function testScore()
    {
        $this->request->setScore(50);
        $this->assertEquals($this->request->getScore(),50);
        $info = $this->request->getInfo();
        $this->assertEquals($info['score'],50);
    }
}
