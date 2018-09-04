<?php

namespace Shieldfy\Callbacks;

use Shieldfy\Config;
use Shieldfy\Http\Dispatcher;

class VendorCallback
{
    public function __construct(Config $config, Dispatcher $dispatcher, $collectors)
    {
        $this->config = $config;
        $this->dispatcher = $dispatcher;
        $this->collectors = $collectors;
        if ($this->collectors['request']->get['shieldfy'] == 'update-vendor') {
            $this->update();
        }
    }

    private function update()
    {
        $response = $this->dispatcher->trigger('update/vendors', [
            'sdk_version'=>$this->config['version'],
            'php_version'=>PHP_VERSION,
            'sapi_type' =>@php_sapi_name(),
            'os_info'=>@php_uname(),
            'disabled_functions'=>(@ini_get('disable_functions') ? @ini_get('disable_functions') : 'NA'),
            'loaded_extensions'=>implode(',', @get_loaded_extensions()),
            'display_errors'=>@ini_get('display_errors'),
        ]);
        $this->save($response);
    }

    private function save($data)
    {
        $data = json_encode($data);
        $data_path = $this->config['paths']['data'];
        if ($this->isJson($data)) {
            file_put_contents($data_path.'/vendors.json', $data);
        }
    }

    private function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}