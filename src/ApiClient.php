<?php

namespace Shieldfy;

use Shieldfy\Exceptions\ExceptionHandler;
use Shieldfy\Exceptions\FailedExtentionLoadingException;
use Shieldfy\Exceptions\ServerErrorException;

class ApiClient
{
    const TIMEOUT = 30;
    private $curl = null;
    private $useragent = 'shieldfy-php/1.0';
    private $baseUrl = '';
    private $keys = [];
    private $errors = [];

    public function __construct()
    {
        if (!extension_loaded('curl')) {
            ExceptionHandler::throwException(new FailedExtentionLoadingException('cURL library is not loaded'));
        }

        $this->curl = curl_init();

        $this->setApiKey(Shieldfy::getAppKeys());
        $this->setBaseUrl(Shieldfy::$endpoint);

        $this->setUserAgent();
        $this->setTimeout(self::TIMEOUT);
        $this->setDefaultOptions();
        $this->setCertificate();
    }

    public function request($url, $body = '')
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
        //print_r($result);
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
            ExceptionHandler::throwException(new ServerErrorException($this->errors['code'].':'.$this->errors['message']));
        }

        return $res;
    }

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

    private function setApiKey($keys)
    {
        $this->keys = $keys;
    }

    private function setBaseUrl($url)
    {
        $this->baseUrl = $url;
    }

    public function setUserAgent()
    {
        $this->setOpt(CURLOPT_USERAGENT, $this->useragent.' (php '.phpversion().' )');
    }

    public function setTimeout($seconds)
    {
        $this->setOpt(CURLOPT_TIMEOUT, $seconds);
    }

    public function setDefaultOptions()
    {
        $this->setOpt(CURLOPT_RETURNTRANSFER, true);
    }

    private function setCertificate()
    {
        $this->setOpt(CURLOPT_SSL_VERIFYPEER, true);
        $this->setOpt(CURLOPT_SSL_VERIFYHOST, 2);
        $this->setOpt(CURLOPT_CAINFO, Shieldfy::getRootDir().'/certificate/cacert.pem');
    }

    private function setupHeaders($hash)
    {
        $this->setOpt(CURLOPT_HTTPHEADER,
            [
                'X-Shieldfy-Api-Key: '.$this->keys['app_key'],
                'X-Shieldfy-Api-Hash: '.$hash,
            ]
        );
    }

    private function calculateBodyHash($body)
    {
        return hash_hmac('sha256', $body, $this->keys['app_secret']);
    }

    public function setOpt($option, $value)
    {
        return curl_setopt($this->curl, $option, $value);
    }

    public function close()
    {
        if (is_resource($this->curl)) {
            curl_close($this->curl);
        }
    }

    public function getError()
    {
        return $this->errors;
    }

    public function getInfo($opt)
    {
        return curl_getinfo($this->curl, $opt);
    }
}
