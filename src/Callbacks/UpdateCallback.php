<?php

namespace Shieldfy\Callbacks;

use Shieldfy\Config;
use Shieldfy\Event;


class UpdateCallback implements CallbackInterface
{
    public static function handle(Config $config, Event $event)
    {
        //update callback
        $response = $this->event->trigger('update');
        if ($response->status == 'success') {
            $data = (array) $response->data;
            if (isset($data['site_rules'])) {
                file_put_contents($config['rootDir'].'/data/site_rules', json_encode($data['site_rules']));
            }
            if (isset($data['pre_rules'])) {
                file_put_contents($config['rootDir'].'/data/pre_rules', json_encode($data['pre_rules']));
            }
            if (isset($data['hard_rules'])) {
                file_put_contents($config['rootDir'].'/data/hard_rules', json_encode($data['hard_rules']));
            }
            if (isset($data['soft_rules'])) {
                file_put_contents($config['rootDir'].'/data/soft_rules', json_encode($data['soft_rules']));
            }
        }
        echo json_encode(['status'=>'success']);
    }
}
