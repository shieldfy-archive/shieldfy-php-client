<?php

namespace Shieldfy;

/**
 * Headers class.
 */
class Headers
{
    /**
     * @var
     */
    protected $config;

    /**
     * Constructor.
     *
     * @param Config $config
     *
     * @return type
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Exposes useful headers in the request.
     *
     * @param array $disabledHeaders
     *
     * @return void
     */
    public function expose()
    {
        /* expose useful headers */
        if (!in_array('x-xss-protection', $this->config['disabledHeaders'])) {
            header('X-XSS-Protection: 1; mode=block');
        }

        if (!in_array('x-content-type-options', $this->config['disabledHeaders'])) {
            header('X-Content-Type-Options: nosniff');
        }

        if (!in_array('x-frame-options', $this->config['disabledHeaders'])) {
            header('X-Frame-Options: SAMEORIGIN');
        }

        // TODO: Check for SSL before enabling.
        //header('Strict-Transport-Security: max-age=31536000')

        // CSP security Header.
        //header('Content-Security-Policy: script-src 'self'')

        header('X-Powered-By: NA');

        $signature = hash_hmac('sha256', $this->config['app_key'], $this->config['app_secret']);

        header('X-Web-Shield: ShieldfyWebShield');
        header('X-Shieldfy-Signature: '.$signature);
    }
}
