<?php

namespace Shieldfy\Queue;

use Shieldfy\Config;
use Shieldfy\Http\ApiClient;

use Shieldfy\Queue\UserConfig;

class ShieldfyJob
{
    protected $config;
    protected $apiClient;

    public function __construct()
    {
        $_config = UserConfig::getData();
        $config = new Config($_config);
        $this->apiClient = new ApiClient($config['endpoint'], $config);
    }

    public function run()
    {
        $files = array_slice(glob(__DIR__.'/../../tmp/cache/*.*'), 0, 5);
        
        foreach ($files as $file) {
            if (file_exists($file)) {
                $data = file_get_contents($file);
                $data = json_decode($data);
                $res = $this->apiClient->request($data->event, json_encode($data->data));
                if ($res->status == 'success') {
                    unlink($file);
                }
            }
        }
    }
}
