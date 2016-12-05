<?php

namespace Shieldfy;

class User
{
    private $userId = ''; //identifier for the visitor
    private $userIp = '';
    private $userAgent = '';

    public function __construct()
    {
        $this->setIP();
        $this->setID();
        $this->setUserAgent();
    }

    public function setID()
    {
        $this->userId = ip2long($this->userIp);
    }

    public function setIp()
    {
        $userIp = '0.0.0.0'; //unknown ip

        if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
            $userIp = $_SERVER['REMOTE_ADDR'];
        }

        if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
            $header = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $userIp = $header[0];
        }

        if (array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
            $userIp = $_SERVER['HTTP_CLIENT_IP'];
        }

        if (array_key_exists('HTTP_X_REAL_IP', $_SERVER)) {
            $userIp = $_SERVER['HTTP_X_REAL_IP'];
        }

        $this->userIp = $userIp;
    }

    public function setUserAgent()
    {
        if (array_key_exists('HTTP_USER_AGENT', $_SERVER)) {
            $this->userAgent = $_SERVER['HTTP_USER_AGENT'];
        }
    }

    public function getID()
    {
        return $this->userId;
    }

    public function getInfo()
    {
        return [
            'id'       => $this->userId,
            'ip'       => $this->userIp,
            'userAgent'=> $this->userAgent,
        ];
    }
}
