<?php

namespace Shieldfy\Callbacks;

use Shieldfy\Config;
use Shieldfy\Event;

class CallbackHandler
{
    private $hooks = [
        'ping'  => PingCallback::class,
        'update'=> UpdateCallback::class,
        'logs'  => LogsCallback::class,
    ];

    protected $config;
    protected $event;

    public function __construct(Config $config, Event $event)
    {
        $this->config = $config;
        $this->event = $event;
    }

    public function catchCallbacks()
    {
        /* hello i am catching the callbacks */
        if (isset($_SERVER['HTTP_X_SHIELDFY_CALLBACK'])) {
            //its callback lets verify it
            $res = $this->verify();
            if (!$res) {
                $this->closeConnection(403, 'Unauthorize Action');
            }
            $hook = $_SERVER['HTTP_X_SHIELDFY_CALLBACK'];
            if (!isset($this->hooks[$hook])) {
                $this->closeConnection(403, 'Invalid Callback');
            }
            //verified lets process
            $callback = $this->hooks[$hook];
            $callback::handle($this->config, $this->event);

            $this->closeConnection();
        }
    }

    private function verify()
    {
        if (!isset($_SERVER['HTTP_X_SHIELDFY_CALLBACK_HASH'])) {
            return false;
        }
        $hash = $_SERVER['HTTP_X_SHIELDFY_CALLBACK_HASH'];
        $keys = Shieldfy::getAppKeys();
        $localHash = hash_hmac('sha256', $keys['app_key'], $keys['app_secret']);
        if ($hash == $localHash) {
            return true;
        }

        return false;
    }

    private function closeConnection($status = 200, $msg = '')
    {
        if ($status == 200 && $msg == '') {
            exit;
        }
        @header($_SERVER['SERVER_PROTOCOL'].' '.$status.' '.$msg.' :: Shieldfy Web Shield ');
        @die($msg.' :: Shieldfy Web Shield');
        exit;
    }
}
