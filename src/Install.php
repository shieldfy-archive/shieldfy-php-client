<?php

namespace Shieldfy;

use Shieldfy\Config;
use Shieldfy\Event;
use Shieldfy\Exceptions\ExceptionHandler;
use Shieldfy\Exceptions\InstallationException;

class Install
{

    /**
     * @var config 
     * @var request
     * @var event
     * @var exceptionHandler
     */
    protected $config;
    protected $request;
    protected $event;
    protected $exceptionHandler;

    /**
     * Constructor
     * @param Config $config 
     * @param Event $event 
     * @param ExceptionHandler $exceptionHandler 
     * @return type
     */
    public function __construct(Config $config,Request $request,Event $event, ExceptionHandler $exceptionHandler)
    {
        $this->config = $config;
        $this->request = $request;
        $this->event = $event;
        $this->exceptionHandler = $exceptionHandler;
    }
    /**
     * run installation if not installed
     *
     * @return bool $installed
     */
    public function run()
    {
        if (!$this->isInstalled()) {
            $this->install();
            return true;
        }
        return false;
    }

    /**
     * check if the script installed or not.
     *
     * @return bool
     */
    private function isInstalled()
    {
        if (file_exists($this->config['rootDir'].'/data/installed')) {
            return true;
        }

        return false;
    }

    /**
     * install the package.
     *
     * @return bool true
     */
    public function install()
    {
        $response = $this->event->trigger('install', [
            'host'  => $this->request->server['HTTP_HOST'],
            'https' => self::isSecure(),
            'ip'    => $this->request->server['SERVER_ADDR'],
            'server'=> [
                'lang'              => 'php',
                'webserver'         => $this->request->server['SERVER_SOFTWARE'],
                'php_version'       => PHP_VERSION,
                'sapi_type'         => @php_sapi_name(),
                'os_info'           => @php_uname(),
                'disabled_functions'=> (@ini_get('disable_functions') ? @ini_get('disable_functions') : 'None'),
                'loaded_extensions' => implode(',', @get_loaded_extensions()),
                'display_errors'    => @ini_get('display_errors'),
                'register_globals'  => (@ini_get('register_globals') ? @ini_get('register_globals') : 'None'),
                'post_max_size'     => @ini_get('post_max_size'),
                'curl'              => @extension_loaded('curl') && @is_callable('curl_init'),
                'fopen'             => @ini_get('allow_url_fopen'),
                'mcrypt'            => @extension_loaded('mcrypt'),
            ],
        ]);

        if ($response->status == 'success') {
            file_put_contents($this->config['rootDir'].'/data/installed', time());
            $data = (array) $response->data;
            if (isset($data['site_rules'])) {
                file_put_contents($this->config['rootDir'].'/data/site_rules', json_encode($data['site_rules']));
            }
            if (isset($data['pre_rules'])) {
                file_put_contents($this->config['rootDir'].'/data/pre_rules', json_encode($data['pre_rules']));
            }
            if (isset($data['hard_rules'])) {
                file_put_contents($this->config['rootDir'].'/data/hard_rules', json_encode($data['hard_rules']));
            }
            if (isset($data['soft_rules'])) {
                file_put_contents($this->config['rootDir'].'/data/soft_rules', json_encode($data['soft_rules']));
            }
        } else {
            ExceptionHandler::throwException(new InstallationException($response->code, $response->message));
        }

        return true;
    }

    /**
     * check if website is using ssl or not.
     *
     * @return bool
     */
    private  function isSecure()
    {
        return
        (!empty($this->request->server['HTTPS']) && $this->request->server['HTTPS'] !== 'off')
        || $this->request->server['SERVER_PORT'] == 443;
    }
}
