<?php
namespace Shieldfy;

use Shieldfy\Config;
use Shieldfy\Exceptions\Exceptionable;
use Shieldfy\Exceptions\Exceptioner;
use Shieldfy\Exceptions\InstallationException;
use Shieldfy\Http\Dispatcher;
use Shieldfy\Collectors\RequestCollector;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

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

        //get important files for scan
        $scanFiles = [];
        foreach ($this->config['scanFiles'] as $file) {
            $filePath = $this->config['paths']['base'].'/'.$file;
            if (file_exists($filePath) && is_readable($filePath)) {
                $scanFiles[$file] = str_replace([" ","\t","\n"], '', file_get_contents($filePath));
            }
        }

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
            'display_errors' => ini_get('display_errors'),
            'scan_files' => $scanFiles
        ]);
        if (!$response) {
            $this->throwException(new InstallationException('Unknown error happened', 200));
            return false;
        }
        if ($response->status == 'error') {
            $this->throwException(new InstallationException($response->message));
            return false;
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
     * @param  array $scanFiles | files to scan for security issues
     */
    private function save(array $data = [], array $scanFiles = [])
    {

        //if not writable , try to chmod it
        if (!is_writable($this->config['paths']['data'])) {
            @chmod($this->config['paths']['data'], 0755);
            if (!is_writable($this->config['paths']['data'])) {
                $this->throwException(new InstallationException('Data folder :'.$this->config['paths']['data'].' Is not writable', 200));
            }
        }

        foreach ($scanFiles as $fileName) {
            $filePath = $this->config['paths']['base'].'/'.$fileName;
            file_put_contents($this->config['paths']['data'].'/'.$fileName.'.sign', md5_file($filePath));
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
