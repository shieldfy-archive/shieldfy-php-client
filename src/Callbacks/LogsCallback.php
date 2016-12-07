<?php

namespace Shieldfy\Callbacks;

use Shieldfy\Config;
use Shieldfy\Event;

class LogsCallback implements CallbackInterface
{
    public static function handle(Config $config, Event $event)
    {
        //verify callback
        $path = realpath($config['rootDir'].'/../log');
        $data = [];
        $contents = scandir($path);
        foreach ($contents as $file) {
            if ($file == '.' || $file == '..' || $file == '.gitignore') {
                continue;
            }
            $data[$file] = file_get_contents($path.'/'.$file);
            @unlink($path.'/'.$file);
        }
        echo json_encode(['status'=>'success', 'message'=>$data]);
    }
}
