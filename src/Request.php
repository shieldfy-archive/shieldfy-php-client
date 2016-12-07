<?php

namespace Shieldfy;

class Request
{
    /**
     * Request method taken from ($_SERVER).
     */ 
    protected $requestMethod;

    /**
     * Query string parameters ($_GET).
     */
    public $get;

    /**
     * Request body parameters ($_POST).
     */
    public $post;

    /**
     * Server and execution environment parameters ($_SERVER).
     */
    public $server;

    /**
     * Cookies ($_COOKIE).
     */
    public $cookies;

    /**
     * Uploaded files ($_FILES).
     */
    public $files;

    /**
     * store request parameter in short way
     */ 
    protected $params;

    /**
     * @var timestamp request creation time
     */
    protected $created;


    /**
     * constructor
     * @param array|array $get 
     * @param array|array $post 
     * @param array|array $server 
     * @param array|array $cookies 
     * @param array|array $files 
     */
    public function __construct($get = [],$post = [],$server = [],$cookies = [],$files = [])
    {
        $this->get = $get;
        $this->post = $post;
        $this->server = $server;
        $this->cookies = $cookies;
        $this->files = $files;
        $this->requestMethod = $server['REQUEST_METHOD'];
        $this->created = time();
        $this->params = $this->prepare();
    }

    /**
     * prepare a short copy of request 
     * @return array $data;
     */
    private function prepare()
    {
        $data = [
            'get' => $this->get,
            'post'=> $this->post,
        ];

        if (isset($this->server['PHP_SELF'])) {
            $data['server']['ps'] = $this->server['PHP_SELF'];
        }
        if (isset($this->server['PATH_INFO'])) {
            $data['server']['pi'] = $this->server['PATH_INFO'];
        }
        if (isset($this->server['REQUEST_URI'])) {
            $data['server']['uri'] = $this->server['REQUEST_URI'];
        }
        if (isset($this->server['HTTP_ORIGIN'])) {
            $data['server']['ho'] = $this->server['HTTP_ORIGIN'];
        }
        if (isset($this->server['HTTP_HOST'])) {
            $data['server']['hh'] = $this->server['HTTP_HOST'];
        }
        if (isset($this->server['HTTP_REFERER'])) {
            $data['server']['r'] = $this->server['HTTP_REFERER'];
        }

        return $data;
    }

    /**
     * get request info
     * @return array info
     */
    public function getInfo()
    {
        return [
            'created' => $this->created,
            'info' => [
                'method' => $this->requestMethod,
                'params' => $this->params
            ]
        ];
    }

}
