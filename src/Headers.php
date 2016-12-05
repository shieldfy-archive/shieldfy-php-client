<?php

namespace Shieldfy;

/**
 * Headers class.
 */
class Headers
{
    /**
     * Exposes useful headers in the request.
     *
     * @param array $disabledHeaders
     *
     * @return void
     */
    public static function expose($disabledHeaders = [])
    {
        /* expose useful headers */
        if (!in_array('x-xss-protection', $disabledHeaders)) {
            header('X-XSS-Protection: 1; mode=block');
        }

        if (!in_array('x-content-type-options', $disabledHeaders)) {
            header('X-Content-Type-Options: nosniff');
        }

        if (!in_array('x-frame-options', $disabledHeaders)) {
            header('X-Frame-Options: SAMEORIGIN');
        }

        // TODO: Check for SSL before enabling.
        //header('Strict-Transport-Security: max-age=31536000')

        // This header loads only local/origin libraries.
        //header('Content-Security-Policy: script-src 'self'')

        header('X-Powered-By: NA');

        $api = Shieldfy::getAppKeys();
        $signature = hash_hmac('sha256', $api['app_key'], $api['app_secret']);

        header('X-Web-Shield: ShieldfyWebShield');
        header('X-Shieldfy-Signature: '.$signature);
    }
}
