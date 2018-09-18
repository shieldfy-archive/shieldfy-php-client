<?php

namespace Shieldfy\Callbacks;

use Shieldfy\Callbacks\Callback;
use Shieldfy\Response\Response;

use Shieldfy\Exceptions\Exceptionable;
use Shieldfy\Exceptions\Exceptioner;
use Shieldfy\Exceptions\InstallationException;

class VendorCallback extends Callback implements Exceptionable
{
    use Response;
    use Exceptioner;

    public function handle()
    {
        $this->respond()->json($this->update());
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
        if ($response->status == 'success') {
            $this->save($response->data);
            return [
                'status' => 'success'
            ];
        }
        return [
            'status' => 'error'
        ];
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
