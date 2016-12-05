<?php
namespace Shieldfy;
class User
{
    private $id = ''; //identifier for the visitor
    private $ip = '';
    private $userAgent = '';

    public function __construct()
    {
        $this->setIP();
        $this->setID();
        $this->setUserAgent();
    }

    public function setID()
    {
        $this->id = ip2long($this->ip);
    }

    public function setIp()
    {
        $ip = '0.0.0.0'; //unknown ip

        if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
          $ip = $_SERVER['REMOTE_ADDR'];
        }

        if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
          $header = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
          $ip = $header[0];
        }

        if (array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
          $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        
        if (array_key_exists('HTTP_X_REAL_IP', $_SERVER)) {
          $ip = $_SERVER['HTTP_X_REAL_IP'];
        }
        
        $this->ip = $ip;
    }

    public function setUserAgent()
    {
        if(array_key_exists('HTTP_USER_AGENT', $_SERVER)){
            $this->userAgent =  $_SERVER['HTTP_USER_AGENT'];
        }
    }

    public function getID()
    {
        return $this->id;
    }

    public function getInfo()
    {
        return [
            'id'=>$this->id,
            'ip'=>$this->ip,
            'userAgent'=>$this->userAgent
        ];
    }

}
