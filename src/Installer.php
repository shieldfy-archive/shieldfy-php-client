<?php
namespace Shieldfy;

use Shieldfy\Config;

use Shieldfy\Exceptions\Exceptionable;
use Shieldfy\Exceptions\Exceptioner;
use Shieldfy\Exceptions\InstallationException;

use Shieldfy\Dispatcher\Dispatchable;
use Shieldfy\Dispatcher\Dispatcher;

use Shieldfy\Collectors\RequestCollector;

class Installer implements Dispatchable, Exceptionable
{
    use Dispatcher;
    use Exceptioner;

    /**
     * @var config
     * @var request
     */
    protected $config;
    protected $request;
    protected $sdk_version;

    public function __construct(RequestCollector $request,Config $config,$sdk_version)
    {
        $this->config = $config;
        $this->request = $request;
        $this->sdk_version = $sdk_version;
    }

    public function run()
    {
        $response = $this->trigger('install',[
            'host'=>$this->request->server['HTTP_HOST'],
            'https'=>$this->request->isSecure(),
            'lang' => 'php',
            'sdk_version'=>$this->sdk_version,
            'server'=> isset($this->request->server['SERVER_SOFTWARE'])?$this->request->server['SERVER_SOFTWARE']:'NA',
            'php_version'=>PHP_VERSION,
            'sapi_type' =>@php_sapi_name(),
            'os_info'=>@php_uname(),
            'disabled_functions'=>(@ini_get('disable_functions') ? @ini_get('disable_functions') : 'NA'),
            'loaded_extensions'=>implode(',', @get_loaded_extensions()),
            'display_errors'=>@ini_get('display_errors'),
        ]);

        if(!$response){
            $this->throwException(new InstallationException('Unknown error happened','000'));
            return;
        }

        if($response->status == 'error'){
            $this->throwException(new InstallationException($response->message,$response->code));
            return;
        }

        if($response->status == 'success') {
            $this->save((array)$response->data);
        }
    }

    private function save(array $data = [])
    {
        $data_path = $this->config['rootDir'].'/data';
        file_put_contents($data_path.'/installed', time());

        if(isset($data['upload'])) file_put_contents($data_path.'/upload', json_encode($data['upload']));
        if(isset($data['request'])) file_put_contents($data_path.'/request', json_encode($data['request']));
        if(isset($data['api'])) file_put_contents($data_path.'/api', json_encode($data['api']));
        if(isset($data['exceptions'])) file_put_contents($data_path.'/exceptions', json_encode($data['exceptions']));
        if(isset($data['query'])) file_put_contents($data_path.'/query', json_encode($data['query']));
        if(isset($data['view'])) file_put_contents($data_path.'/view', json_encode($data['view']));
    }

}
