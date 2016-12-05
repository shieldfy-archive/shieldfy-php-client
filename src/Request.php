<?php
namespace Shieldfy;
class Request
{
    protected $requestMethod = '';
    protected $ajax = 0;
    protected $params;
    public function __construct()
    {
        $this->requestMethod = $_SERVER['REQUEST_METHOD'];
        $this->setParams();
    }

    private function setParams()
    {
        $params = $this->prepare([
            'get'=>$_GET,
            'post'=>$_POST,
            'server'=>$_SERVER
        ]);
        $this->params =  $params;
    }
    private function prepare($params){
        $data = [
            'get'=>$params['get'],
            'post'=>$params['post']
        ];
    
        if(isset($params['server']['PHP_SELF'])){
            $data['server']['ps'] = $params['server']['PHP_SELF'];
        }
        if(isset($params['server']['PATH_INFO'])){
            $data['server']['pi'] = $params['server']['PATH_INFO'];
        }
        if(isset($params['server']['REQUEST_URI'])){
            $data['server']['uri'] = $params['server']['REQUEST_URI'];
        }
        if(isset($params['server']['HTTP_ORIGIN'])){
            $data['server']['ho'] = $params['server']['HTTP_ORIGIN'];
        }
        if(isset($params['server']['HTTP_HOST'])){
            $data['server']['hh'] = $params['server']['HTTP_HOST'];
        }
        if(isset($params['server']['HTTP_REFERER'])){
            $data['server']['r'] = $params['server']['HTTP_REFERER'];
        }
        return $data;
    }


    public function getInfo()
    {
        return [
            'method'=>$this->requestMethod, 
            'params'=>$this->getParams() 
        ];
    }
    public function getParams(){
        return $this->params;
    }
}
