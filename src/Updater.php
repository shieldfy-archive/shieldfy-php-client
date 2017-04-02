<?php
namespace Shieldfy;

use Shieldfy\Config;

use Shieldfy\Exceptions\Exceptionable;
use Shieldfy\Exceptions\Exceptioner;
use Shieldfy\Exceptions\InstallationException;

use Shieldfy\Dispatcher\Dispatchable;
use Shieldfy\Dispatcher\Dispatcher;

use Shieldfy\Collectors\RequestCollector;

class Updater implements Dispatchable, Exceptionable
{
    use Dispatcher;
    use Exceptioner;

    /**
     * @var config
     */
    protected $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Run update
     */
    public function run()
    {
        $response = $this->trigger('update',[
            'sdk_version'=>$this->config['version'],
            'php_version'=>PHP_VERSION,
            'sapi_type' =>@php_sapi_name(),
            'os_info'=>@php_uname(),
            'disabled_functions'=>(@ini_get('disable_functions') ? @ini_get('disable_functions') : 'NA'),
            'loaded_extensions'=>implode(',', @get_loaded_extensions()),
            'display_errors'=>@ini_get('display_errors'),
        ]);

        if(!$response){
            $this->throwException(new InstallationException('Unknown error happened',200));
            return false;
        }

        if($response->status == 'error'){
            $this->throwException(new InstallationException($response->message,$response->code));
            return false;
        }

        if($response->status == 'success') {
            $this->save((array)$response->data);
        }
        return true;
    }

    /**
     * Save grapped data
     * @param  [type] $data
     */
    private function save(array $data = [])
    {
        $data_path = $this->config['rootDir'].'/data';
        if(!file_exists($data_path.'/installed')) file_put_contents($data_path.'/installed', time());
        file_put_contents($data_path.'/updated', time());

        if(isset($data['upload'])) file_put_contents($data_path.'/upload.json', $data['upload']);
        if(isset($data['request'])) file_put_contents($data_path.'/request.json', $data['request']);
        if(isset($data['api'])) file_put_contents($data_path.'/api.json', $data['api']);
        if(isset($data['exceptions'])) file_put_contents($data_path.'/exceptions.json', $data['exceptions']);
        if(isset($data['query'])) file_put_contents($data_path.'/query.json', $data['query']);
        if(isset($data['view'])) file_put_contents($data_path.'/view.json', $data['view']);
    }

}
