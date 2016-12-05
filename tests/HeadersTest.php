<?php

namespace Shieldfy\Test;

use PHPUnit\Framework\TestCase;
use Shieldfy\Headers;
use Shieldfy\Shieldfy;

class HeadersTest extends TestCase
{
    public function setup()
    {
        $config = [
            'app_key'   => 'testKey',
            'app_secret'=> 'testSecret',
        ];
        Shieldfy::setConfig($config);
    }

    /**
     * @runInSeparateProcess
     */
    public function testExpose()
    {
        Headers::expose();

        if (function_exists('xdebug_get_headers')) {
            $headers = xdebug_get_headers(1);
            //expected headers
            $api = Shieldfy::getAppKeys();
            $signature = hash_hmac('sha256', $api['app_key'], $api['app_secret']);
            $expectedHeaders = [
                'X-XSS-Protection: 1; mode=block',
                'X-Content-Type-Options: nosniff',
                'X-Frame-Options: SAMEORIGIN',
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
