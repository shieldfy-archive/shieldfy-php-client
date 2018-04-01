<?php
namespace Shieldfy\Callbacks;

use Shieldfy\Callbacks\Callback;
use Shieldfy\Response\Response;

use Shieldfy\Exceptions\Exceptionable;
use Shieldfy\Exceptions\Exceptioner;
use Shieldfy\Exceptions\InstallationException;

use Shieldfy\Dispatcher\Dispatchable;
use Shieldfy\Dispatcher\Dispatcher;

class UpdateCallback extends Callback implements Dispatchable, Exceptionable
{
    use Response;
    use Dispatcher;
    use Exceptioner;

    public function handle()
    {
        $this->respond()->json($this->update());
    }

    /**
     * Run update
     */
    private function update()
    {
        $response = $this->trigger('update', [
            'sdk_version'=>$this->config['version'],
            'php_version'=>PHP_VERSION,
            'sapi_type' =>@php_sapi_name(),
            'os_info'=>@php_uname(),
            'disabled_functions'=>(@ini_get('disable_functions') ? @ini_get('disable_functions') : 'NA'),
            'loaded_extensions'=>implode(',', @get_loaded_extensions()),
            'display_errors'=>@ini_get('display_errors'),
        ]);

        if (!$response) {
            return [
                'status' 	=> 'error',
                'code'   	=> '000',
                'message' 	=> 'Unknown error happened'
            ];
        }

        if ($response->status == 'error') {
            return [
                'status' 	=> 'error',
                'code'   	=> $response->code,
                'message' 	=> $response->message
            ];
        }

        if ($response->status == 'success') {
            $this->save((array)$response->data);
        }

        return [
            'status' => 'success'
        ];
    }

    /**
     * Save rules data
     * Rules is used to identify threats across application layers
     * Stored only in vendors -> shieldfy -> data folder
     * @param  [type] $data
     */
    private function save(array $data = [])
    {
        //if not writable , try to chmod it
        if (!is_writable($this->config['paths']['data'])) {
            @chmod($this->config['paths']['data'], 0755);
            if (!is_writable($this->config['paths']['data'])) {
                throw new InstallationException('Data folder :'.$this->config['paths']['data'].' Is not writable', 200);
            }
        }

        $data_path = $this->config['paths']['data'];
        if (!file_exists($data_path.'/installed')) {
            file_put_contents($data_path.'/installed', time());
        }

        foreach ($data['rules'] as $ruleName => $ruleContent):
            $content = base64_decode($ruleContent);
        if ($this->isJson($content)) {
            file_put_contents($data_path.'/'.$ruleName.'.json', $content);
        }
        endforeach;
    }

    private function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}
