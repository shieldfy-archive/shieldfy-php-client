<?php

namespace Shieldfy\Callbacks;

use Shieldfy\Event;
use Shieldfy\Shieldfy;

class UpdateCallback implements CallbackInterface
{
    public static function handle()
    {
        //update callback
        $response = Event::trigger('update');
        if ($response->status == 'success') {
            $data = (array) $response->data;
            if (isset($data['site_rules'])) {
                file_put_contents(Shieldfy::getRootDir().'/data/site_rules', json_encode($data['site_rules']));
            }
            if (isset($data['pre_rules'])) {
                file_put_contents(Shieldfy::getRootDir().'/data/pre_rules', json_encode($data['pre_rules']));
            }
            if (isset($data['hard_rules'])) {
                file_put_contents(Shieldfy::getRootDir().'/data/hard_rules', json_encode($data['hard_rules']));
            }
            if (isset($data['soft_rules'])) {
                file_put_contents(Shieldfy::getRootDir().'/data/soft_rules', json_encode($data['soft_rules']));
            }
        }
        echo json_encode(['status'=>'success']);
    }
}
