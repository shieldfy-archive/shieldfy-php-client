<?php
namespace Shieldfy\Verified;

use Shieldfy\Config;
use Shieldfy\Response\Notification;

class CheckInstall
{
    public function __construct(Config $config, $collectors)
    {
        $this->config = $config;
        $this->collectors = $collectors;
        $this->notification = new Notification;
    }
    public function run($message, $status = true)
    {
        if (!isset($this->collectors['request']->get['shieldfy'])) {
            return;
        }
        if ($this->collectors['request']->get['shieldfy'] != 'verified') {
            return;
        }

        $hash = $this->collectors['request']->get['hash'];
        $appHash = hash_hmac('sha256', $this->config['app_secret'], $this->config['app_key']);

        // check of keys
        if ($hash !== $appHash) {
            $this->notification->error([
                'message' => 'There is an error in the installation keys',
            ]);
            return;
        }

        // verified install
        $notificationType = 'success';
        if (!$status) {
            $notificationType = 'error';
        }
        $this->notification->$notificationType([
            'message' => $message
        ]);
    }
}
