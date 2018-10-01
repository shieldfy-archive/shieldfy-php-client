<?php
namespace Shieldfy\Collectors;

class RequestCollector implements Collectable
{
    /**
     * Request method taken from ($_SERVER).
     */
    public $requestMethod;

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
     * @var timestamp request creation time
     */
    protected $created;

    /**
     * @var request score
     */
    protected $score = 0;

    /**
     * constructor.
     *
     * @param array|array $get
     * @param array|array $post
     * @param array|array $server
     * @param array|array $cookies
     * @param array|array $files
     */
    public function __construct($get = [], $post = [], $server = [], $cookies = [], $files = [])
    {
        $this->get = $get;
        $this->post = $post;
        $this->server = $server;
        $this->cookies = $cookies;
        $this->files = $files;
        $this->requestMethod = (isset($server['REQUEST_METHOD']))?$server['REQUEST_METHOD']:'get';
        $this->created = time();
    }

    /**
     * Set user score.
     *
     * @param int $score
     */
    public function setScore($score)
    {
        $this->score = $score;
    }

    /**
     * Get user score.
     *
     * @return int $score
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * check if request is done through ssl or not.
     *
     * @return bool
     */
    public function isSecure()
    {
        return
        (!empty($this->server['HTTPS']) && $this->server['HTTPS'] !== 'off')
        || $this->server['SERVER_PORT'] == 443;
    }

    private function prepareRequestParameter($key, $param)
    {
        return $this->prepareRequestParameterRecursive([
            $key=>$param
        ]);
    }
    private function prepareRequestParameterRecursive($params, $prefix = '', $data = [])
    {
        foreach ($params as $key=> $value):
            if (!is_array($value)) {
                $data[$prefix.$key] = $value;
            } else {
                $data = array_merge($data, $this->prepareRequestParameterRecursive($value, $prefix.$key.'.'));
            }
        endforeach;
        return $data;
    }

    public function getHost()
    {
        return (isset($this->server['HTTP_HOST']))? $this->server['HTTP_HOST'] : 'N/A' ;
    }

    /**
     * get request info.
     * @TODO Add a function to strip sensitive data before reporting it (e.g., passwords, tokens, credit cards, etc).
     * @return array info
     */
    public function getInfo($parameter = '')
    {
        $info = [
            'method'        => $this->requestMethod,
            'created'       => $this->created,
            'score'         => $this->score
        ];
        $info['uri'] = $this->server['REQUEST_URI'];
        if ($parameter == '' || $parameter == 'get') {
            $info['get'] = $this->prepareRequestParameter('get', $this->get);
        }
        if ($parameter == '' || $parameter == 'post') {
            $info['post'] = $this->prepareRequestParameter('post', $this->post);
        }
        if ($parameter == '' || $parameter == 'server') {
            $info['server'] = $this->prepareRequestParameter('server', $this->server);
        }
        if ($parameter == '' || $parameter == 'cookies') {
            $info['cookies'] = $this->prepareRequestParameter('cookies', $this->cookies);
        }
        if ($parameter == '' || $parameter == 'files') {
            $info['files'] = $this->prepareRequestParameter('files', $this->files);
        }

        return $info;
    }

    public function getProtectedInfo()
    {
        $info = $this->getInfo();

        unset($info['server']);
        unset($info['cookies']);
        unset($info['files']);

        // TODO: Add a function to strip sensitive data before reporting it (e.g., passwords, tokens, credit cards, etc).
        return $info;
    }

    public function getShortInfo()
    {
        return [
            'method' => $this->requestMethod,
            'uri'    => (isset($this->server['REQUEST_URI']))? $this->server['REQUEST_URI'] : ''
        ];
    }
}
