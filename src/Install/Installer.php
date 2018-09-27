<?php
namespace Shieldfy\Install;

use Shieldfy\Config;
use Composer\Script\Event;
use Shieldfy\Http\Dispatcher;
use Shieldfy\Collectors\RequestCollector;
use Shieldfy\Exceptions\Exceptionable;
use Shieldfy\Exceptions\Exceptioner;
use Shieldfy\Exceptions\InstallationException;

class Installer implements Exceptionable
{
    use Exceptioner;
    /**
     * @var config
     * @var dispatcher
     * @var request
     */
    protected $request;
    protected $dispatcher;
    protected $config;

    /**
     * @param RequestCollector $request
     * @param Config           $config
     */
    public function __construct(RequestCollector $request, Dispatcher $dispatcher, Config $config)
    {
        $this->request = $request;
        $this->dispatcher = $dispatcher;
        $this->config = $config;
    }
    /**
     * Run installation
     */
    public function run()
    {
        $response = $this->dispatcher->trigger('install', [
            'host' => $this->request->server['HTTP_HOST'],
            'https' => $this->request->isSecure(),
            'lang' => 'php',
            'sdk_version' => $this->config['version'],
            'server' => isset($this->request->server['SERVER_SOFTWARE']) ? $this->request->server['SERVER_SOFTWARE'] : 'NA',
            'php_version' => PHP_VERSION,
            'sapi_type' => php_sapi_name(),
            'os_info' => php_uname(),
            'disabled_functions' => ini_get('disable_functions') ?: 'NA',
            'loaded_extensions' => implode(',', get_loaded_extensions()),
            'display_errors' => ini_get('display_errors')
        ]);
        if (!$response) {
            throw new InstallationException('Unknown error happened', 200);
        }
        if ($response->status == 'error') {
            throw new InstallationException($response->message);
        }
        if ($response->status == 'success') {
            $this->save((array)$response->data);
        }
        return true;
    }

    /**
     * Save rules data
     * Rules is used to identify threats across application layers
     * Stored only in vendors -> shieldfy -> data folder
     * @param  array $data | rules data
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
        file_put_contents($data_path.'/installed', time());

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
