<?php
namespace Shieldfy\Verified;

use Shieldfy\Config;

class CheckInstall
{
    public function __construct(Config $config)
    {
        $this->config = $config;
    }
    public function run($message)
    {
        $hash = $this->send($this->collectors['request']->get['hash']);
        $appHash = hash_hmac('sha256', $this->config['app_secret'], $this->config['app_key']);
        if ($this->collectors['request']->get['shieldfy'] == 'verified' && $hash === $appHash) {
            echo '<span style="background: #333;color: #fff;font-size: 15px;font-family: sans-serif;padding: 3px 5px">'. $message .'</span>';
        }
    }
}
