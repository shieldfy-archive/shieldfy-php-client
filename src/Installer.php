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

    /**
     * @param RequestCollector $request [description]
     * @param Config           $config  [description]
     */
    public function __construct(RequestCollector $request, Config $config)
    {
        $this->config = $config;
        $this->request = $request;
    }

    /**
     * Run installation
     */
    public function run()
    {
        $response = $this->trigger('install', [
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
        ]);

        if (!$response) {
            $this->throwException(new InstallationException('Unknown error happened', 200));
            return false;
        }

        if ($response->status == 'error') {
            $this->throwException(new InstallationException($response->message, $response->code));
            return false;
        }

        if ($response->status == 'success') {
            $this->save((array)$response->data);
        }
        return true;
    }

    /**
     * Save grapped data
     * @param  array $data
     */
    private function save(array $data = [])
    {
        if (!is_writable($this->config['dataDir'])) {
            mkdir($this->config['rootDir'].'/data2', 0700);
            $this->config['dataDir'] = $this->config['rootDir'].'/data2';
        }
        $data_path = $this->config['dataDir'];

        file_put_contents($data_path.'/installed', time());

        if (isset($data['upload'])) {
            file_put_contents($data_path.'/upload.json', $data['upload']);
        }
        if (isset($data['request'])) {
            file_put_contents($data_path.'/request.json', $data['request']);
        }
        if (isset($data['api'])) {
            file_put_contents($data_path.'/api.json', $data['api']);
        }
        if (isset($data['exceptions'])) {
            file_put_contents($data_path.'/exceptions.json', $data['exceptions']);
        }
        if (isset($data['query'])) {
            file_put_contents($data_path.'/query.json', $data['query']);
        }
        if (isset($data['view'])) {
            file_put_contents($data_path.'/view.json', $data['view']);
        }
    }

    public static function chmod()
    {
        return chmod("tmp/", 0777);
    }
}
