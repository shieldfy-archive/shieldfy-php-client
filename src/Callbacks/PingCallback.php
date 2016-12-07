<?php

namespace Shieldfy\Callbacks;

use Shieldfy\Config;
use Shieldfy\Event;


class PingCallback implements CallbackInterface
{
    public static function handle(Config $config, Event $event)
    {
        //ping callback
        echo json_encode(['status'=>'success', 'message'=>$config['version']]);
    }
}
