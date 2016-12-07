<?php

namespace Shieldfy\Callbacks;

use Shieldfy\Config;
use Shieldfy\Event;

interface CallbackInterface
{
    public static function handle(Config $config, Event $event);
}
