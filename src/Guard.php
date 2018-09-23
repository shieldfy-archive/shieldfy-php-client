<?php
namespace Shieldfy;

use PDO;
use Shieldfy\Config;
use Shieldfy\Events;
use Shieldfy\Session;
use Shieldfy\Installer;
use Shieldfy\Http\ApiClient;
use Shieldfy\Http\Dispatcher;
use Shieldfy\Monitors\MonitorsBag;
use Shieldfy\Callbacks\CallbackHandler;
use Shieldfy\Collectors\CodeCollector;
use Shieldfy\Collectors\UserCollector;
use Shieldfy\Collectors\RequestCollector;
use Shieldfy\Collectors\ExceptionsCollector;
use Shieldfy\Collectors\PDO\TraceablePDO;
use Shieldfy\Exceptions\InstallationException;
// Verified
use Shieldfy\Verified\CheckInstall;

class Guard
{
    /**
     * @var Singleton Reference to singleton class instance
     */
    private static $instance = null;

    /**
     * @var Api Endpoint
     * @var Version
     * @var Config
     * @var Dispatcher
     * @var Collectors
     * @var Session
     * @var Events
     */
    public $endpoint = 'https://api.shieldfy.com/v1';
    public $version = '3.0.0';
    public $config = null;
    public $dispatcher = null;
    public $collectors = [];
    public $session = null;
    public $events = null;

    /**
     * Initialize Shieldfy guard.
     *
     * @param array $config
     * @return object
     */
    public static function init(array $config = [])
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        return self::$instance = new self($config);
    }

    /**
     * Create a new Guard Instance
     * @param array $userConfig
     * return initialized guard
     */
    private function __construct(array $userConfig)
    {

        //set config container
        $userConfig['version'] = $this->version;
        $this->config = new Config($userConfig);

        //overwrite the endpoint
        if (isset($this->config['endpoint'])) {
            $this->endpoint = $this->config['endpoint'];
        }


        //set Dispatcher
        $apiClient = new ApiClient($this->endpoint, $this->config);
        $this->dispatcher = new Dispatcher($this->config, $apiClient);


        //starting collectors
        $this->collectors = $this->startCollecting();


        //starting events
        $this->events = new Events;


        //catch callbacks
        $this->catchCallbacks($this->collectors['request'], $this->config);

        //check the installation
        $CheckInstall = new CheckInstall($this->config, $this->collectors);
        if (!$this->isInstalled()) {
            try {
                (new Installer($this->collectors['request'], $this->dispatcher, $this->config))->run();
                $CheckInstall->run('The installation process is successful');
            } catch (InstallationException $e) {
                $CheckInstall->run($e->message, false);
                return;
            }
        } else {
            $CheckInstall->run('The protection system stable');
        }

        //start shieldfy guard
        $this->startGuard();
    }

    /**
     * Starting Guard
     */
    private function startGuard()
    {

        //starting session
        $this->session = new Session(
                                $this->collectors['user'],
                                $this->collectors['request'],
                                $this->dispatcher,
                                $this->events
                        );


        register_shutdown_function([$this,'flush']);

        //expose essential headers
        $this->exposeHeaders();

        //starting monitors
        $monitors = new MonitorsBag($this->config, $this->session, $this->dispatcher, $this->collectors, $this->events);
        $monitors->run();
    }


    /**
     * Start Collecting data needed
     * @return Array CollectorsBag
     */
    private function startCollecting()
    {
        $exceptionsCollector = new ExceptionsCollector($this->config, $this->dispatcher);
        $requestCollector = new RequestCollector($_GET, $_POST, $_SERVER, $_COOKIE, $_FILES);
        $userCollector = new UserCollector($requestCollector);
        $codeCollector = new CodeCollector($this->config);

        return [
            'exceptions' => $exceptionsCollector,
            'request'    => $requestCollector,
            'user'       => $userCollector,
            'code'       => $codeCollector
        ];
    }

    /**
     * Catch callbacks from Shieldfy API
     * @param  RequestCollector $request
     * @param  Config           $config
     * @return void
     */
    public function catchCallbacks(RequestCollector $request, Config $config)
    {
        (new CallbackHandler($request, $config, $this->dispatcher))->catchCallback();
    }

    /**
     * check if guard installed
     * @return boolean
     */
    public function isInstalled()
    {
        if (file_exists($this->config['paths']['data'].'/installed')) {
            return true;
        }
        return false;
    }

    /**
     * flush data to the API
     */
    public function flush()
    {
        $this->session->flush();
    }

    /* attach pdo database */
    public function attachPDO(PDO &$pdo)
    {
        return $pdo = new TraceablePDO($pdo, $this->events);
    }


    /**
     * Expose useful headers
     * @return void
     */
    private function exposeHeaders()
    {
        if (function_exists('header_remove')) {
            header_remove('x-powered-by');
        } else {
            header('x-powered-by: unknown');
        }
        if ($this->config['headers']) {
            foreach ($this->config['headers'] as $header => $value) {
                if ($value === false) {
                    continue;
                }
                header($header.': '.$value);
            }
        }

        $signature = hash_hmac('sha256', $this->config['app_key'], $this->config['app_secret']);
        header('X-Web-Shield: ShieldfyWebShield');
        header('X-Shieldfy-Signature: '.$signature);
    }

    /* singelton protection */
    protected function __clone()
    {
    }
    protected function __wakeup()
    {
    }
}
