<?php

namespace Shieldfy;

use Shieldfy\Config;
use Shieldfy\Exceptions\ExceptionHandler;
use Shieldfy\Exceptions\FailedExtentionLoadingException;
use Shieldfy\Exceptions\ServerErrorException;

class ApiClient
{
    /**
     * Const TIMEOUT Connectio Timeout
     */ 
    const TIMEOUT = 30;
    /**
     * @var Config
     * @var ExceptionHandler
     */
    protected $config;
    protected $exceptionHandler;

    /**
     * curl private vars
     */
    private $curl = null;
    private $useragent = 'shieldfy-php/1.0';
    private $baseUrl = '';
    private $keys = [];
    private $errors = [];

    /**
     * constructor
     * @param Config $config 
     * @param ExceptionHandler $exceptionHandler 
     * @return void
     */
    public function __construct(Config $config, ExceptionHandler $exceptionHandler)
    {

        $this->config = $config;
        $this->exceptionHandler = $exceptionHandler;

        if (!extension_loaded('curl')) {
            $this->exceptionHandler->throwException(new FailedExtentionLoadingException('cURL library is not loaded'));
        }

        $this->curl = curl_init();

        $this->setApiKey([
            'app_key'=>$this->config['app_key'],
            'app_secret'=>$this->config['app_secret']
        ]);
        $this->setBaseUrl($this->config['apiEndpoint']);

        $this->setUserAgent();
        $this->setTimeout(self::TIMEOUT);
        $this->setDefaultOptions();
        $this->setCertificate();
    }

    /**
     * Make the actual curl request
     * @param string $url 
     * @param mixed $body 
     * @return mixed $res
     */
    public function request($url, $body)
    {
        $hash = $this->calculateBodyHash($body);
        $this->setupHeaders($hash);

        if (!empty($body)) {
            $postdata = [
                'body'=> $body,
            ];
            $this->setOpt(CURLOPT_POST, count($postdata));
            $postdata = http_build_query(
                $postdata
            );
            $this->setOpt(CURLOPT_POSTFIELDS, $postdata);
        }

        $this->setOpt(CURLOPT_URL, $this->baseUrl.$url);

        $result = curl_exec($this->curl);
        if ($error = curl_error($this->curl)) {
            $this->errors = [
                'code'   => curl_errno($this->curl),
                'message'=> $error,
            ];

            return false;
        }
        $this->close();

        $res = $this->parseResult($result);
        if (!$res) {
            $this->exceptionHandler->throwException(new ServerErrorException($this->errors['code'].':'.$this->errors['message']));
        }

        return $res;
    }

    /**
     * parse the result
     * @param mixed $result 
     * @return mixed $res
     */
    public function parseResult($result)
    {
        $res = json_decode($result);
        if (!$res) {
            $this->errors = [
                'code'   => '001',
                'message'=> 'Unexpected Server Response',
            ];
            return false;
        }

        if ($res->status == 'error') {
            $this->errors = [
                'code'   => $res->code,
                'message'=> $res->message,
            ];

            return false;
        }

        return $res;
    }

    /**
     * Set Api Keys
     * @param type $keys 
     */
    private function setApiKey($keys)
    {
        $this->keys = $keys;
    }

    /**
     * Set Base Url
     * @param type $url 
     */
    private function setBaseUrl($url)
    {
        $this->baseUrl = $url;
    }

    /**
     * Set UserAgent
     */
    public function setUserAgent()
    {
        $this->setOpt(CURLOPT_USERAGENT, $this->useragent.' (php '.phpversion().' )');
    }

    /**
     * Set Timeout
     * @param integer $seconds 
     */
    public function setTimeout($seconds)
    {
        $this->setOpt(CURLOPT_TIMEOUT, $seconds);
    }

    /**
     * Set default options
     * @return type
     */
    public function setDefaultOptions()
    {
        $this->setOpt(CURLOPT_RETURNTRANSFER, true);
    }

    /**
     * Set SSL Certificates
     */
    private function setCertificate()
    {
        $this->setOpt(CURLOPT_SSL_VERIFYPEER, true);
        $this->setOpt(CURLOPT_SSL_VERIFYHOST, 2);
        $this->setOpt(CURLOPT_CAINFO, $this->config['rootDir'].'/certificate/cacert.pem');
    }

    /**
     * Setup Authentication Headers
     * @param String $hash 
     */
    private function setupHeaders($hash)
    {
        $this->setOpt(CURLOPT_HTTPHEADER,
            [
                'X-Shieldfy-Api-Key: '.$this->keys['app_key'],
                'X-Shieldfy-Api-Hash: '.$hash,
            ]
        );
    }

    /**
     * Calculate Body Hash for authentication
     * @param mixed $body 
     * @return string calculated hash
     */
    private function calculateBodyHash($body)
    {
        return hash_hmac('sha256', $body, $this->keys['app_secret']);
    }

    /**
     * set curl options
     * @param mixed $option 
     * @param mixed $value 
     * @return boolean
     */
    public function setOpt($option, $value)
    {
        return curl_setopt($this->curl, $option, $value);
    }

    /**
     * Close curl connection
     */
    public function close()
    {
        if (is_resource($this->curl)) {
            curl_close($this->curl);
        }
    }

    /**
     * Get curl errors
     * @return mixed $errors
     */
    public function getError()
    {
        return $this->errors;
    }

    /**
     * Get curl info
     * @param type $opt 
     * @return type
     */
    public function getInfo($opt)
    {
        return curl_getinfo($this->curl, $opt);
    }
}
