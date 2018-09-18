<?php
namespace Shieldfy\Verified;

use Shieldfy\Config;

class CheckInstall
{
    public function __construct(Config $config, $collectors)
    {
        $this->config = $config;
        $this->collectors = $collectors;
    }
    public function theme($message)
    {
        $html = '<div style="position: fixed;top: 0;left: 0;width: 500px;background: #000000db;color: #fff;font-size: 15px;font-family: sans-serif;text-align: center;padding: 15px">';
        $html .= '<p style="color: #fff; font-size:15px;">' . $message . '</p>';
        $html .= '</div>';
        return $html;
    }
    public function run($message)
    {
        $hash = $this->collectors['request']->get['hash'];
        $appHash = hash_hmac('sha256', $this->config['app_secret'], $this->config['app_key']);

        // verified install
        if ($this->collectors['request']->get['shieldfy'] == 'verified' && $hash === $appHash) {
            echo $this->theme($message);
        }

        // check of keys
        if ($hash !== $appHash) {
            echo $this->theme('There is an error in the installation keys');
        }
    }
}
