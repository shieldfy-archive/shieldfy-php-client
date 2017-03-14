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
    protected $score;

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
        $this->requestMethod = $server['REQUEST_METHOD'];
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
    private function isSecure()
    {
        return
        (!empty($this->server['HTTPS']) && $this->server['HTTPS'] !== 'off')
        || $this->server['SERVER_PORT'] == 443;
    }

    private function prepareRequestParameter($key ,$param)
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

    /**
     * get request info.
     * @TODO add function to strip sensetive data before report it (ex: passwords , tokens , creditcards ... )
     * @return array info
     */
    public function getInfo($parameter = '')
    {
        $info = [
            'method'        => $this->requestMethod,
            'created'       => $this->created,
            'score'         => $this->score
        ];

        if($parameter == '' || $parameter == 'get') $info['get'] = $this->prepareRequestParameter('get',$this->get);
        if($parameter == '' || $parameter == 'post') $info['post'] = $this->prepareRequestParameter('post',$this->post);
        if($parameter == '' || $parameter == 'server') $info['server'] = $this->prepareRequestParameter('server',$this->server);
        if($parameter == '' || $parameter == 'cookies') $info['cookies'] = $this->prepareRequestParameter('cookies',$this->cookies);
        if($parameter == '' || $parameter == 'files') $info['files'] = $this->prepareRequestParameter('files',$this->files);

        return $info;

    }
}
