<?php

namespace Shieldfy\Test;

use PHPUnit\Framework\TestCase;
use Shieldfy\Config;
use Shieldfy\Headers;

class HeadersTest extends TestCase
{
	protected $config;

	public function setup()
	{
		$this->config = new Config;
		$this->config['app_key'] = 'testKey';
		$this->config['app_secret'] = 'testSecret';
		$this->config['disabledHeaders'] = [];
	}
	/**
     * @runInSeparateProcess
     */
    public function testExpose()
    {

        $headers = new Headers($this->config);
        $headers->expose();
        if (function_exists('xdebug_get_headers')) {
            $headers = xdebug_get_headers(1);
            //expected headers
            
            $signature = hash_hmac('sha256', $this->config['app_key'], $this->config['app_secret']);
            $expectedHeaders = [
                'X-XSS-Protection: 1; mode=block',
                'X-Content-Type-Options: nosniff',
                'X-Frame-Options: SAMEORIGIN',
                'X-Powered-By: NA',
                'X-Web-Shield: ShieldfyWebShield',
                'X-Shieldfy-Signature: '.$signature,
            ];
            //print_r($headers);
            foreach ($expectedHeaders as $expectedHeader) {
                $this->assertContains($expectedHeader, $headers);
            }
        }
    }

    /**
     * @runInSeparateProcess
     */
    public function testPartialExpose()
    {
    	$this->config['disabledHeaders'] = [
    		'x-frame-options'
    	];
        $headers = new Headers($this->config);
        $headers->expose();
        
        if (function_exists('xdebug_get_headers')) {
            $headers = xdebug_get_headers(1);
            //expected headers            
            $signature = hash_hmac('sha256', $this->config['app_key'], $this->config['app_secret']);
            $expectedHeaders = [
                'X-XSS-Protection: 1; mode=block',
                'X-Content-Type-Options: nosniff',
                'X-Powered-By: NA',
                'X-Web-Shield: ShieldfyWebShield',
                'X-Shieldfy-Signature: '.$signature,
            ];

            foreach ($expectedHeaders as $expectedHeader) {
                $this->assertContains($expectedHeader, $headers);
            }
        }
    }
}