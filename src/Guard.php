<?php
namespace shieldfy;

use PDO;
use Shieldfy\Config;
use Shieldfy\Installer;
use Shieldfy\Session;
use Shieldfy\Callbacks\CallbackHandler;
use Shieldfy\Cache\CacheManager;
use Shieldfy\Monitors\MonitorsBag;
use Shieldfy\Collectors\UserCollector;
use Shieldfy\Collectors\RequestCollector;
use Shieldfy\Collectors\ExceptionsCollector;
use Shieldfy\Collectors\CodeCollector;
use Shieldfy\Collectors\QueriesCollector;
use Shieldfy\Exceptions\ExceptionHandler;

class Guard
{
    /**
     * @var Singleton Reference to singleton class instance
     */
    private static $instance;

    /**
     * @var api endpoint
     */
    public $apiEndpoint = 'http://api.flash.app';

    /**
     * @var version
     */
    public $version = '2.1.0';

    /**
     * Default configurations items.
     *
     * @var array
     */
    protected $defaults = [
        'debug'          => false, //debug status [true,false]
        'action'         => 'block', //response action [block, silent]
        'headers'         => [ //list of available headers to expose
            'X-XSS-Protection'       =>  '1; mode=block',
            'X-Content-Type-Options' =>  'nosniff',
            'X-Frame-Options'        =>  'SAMEORIGIN'
        ],
        'disable'       =>  [] //list of disabled monitors
    ];

    /**
     * @var Config $config
     * @var CacheManager $cache
     * @var Session $session
     * @var array $collectors
     */
    protected $config;
    protected $cache;
    protected $session;
    protected $collectors;

    /**
     * Initialize Shieldfy guard.
     *
     * @param array $config
     * @param CacheInterface $cache
     * @return object
     */
    public static function init(array $config, $cache = null)
    {
        if (!isset(self::$instance)) {
            self::$instance = new self($config, $cache);
        }
        return self::$instance;
    }

    /**
     * Create a new Guard Instance
     * @param array $config
     * @param CacheInterface $cache
     * return initialized guard
     */
    public function __construct(array $config, $cache = null)
    {
        //set config container
        $this->config = new Config($this->defaults, array_merge($config, [
            'apiEndpoint' => $this->apiEndpoint,
            'rootDir'     => __dir__,
            'version'     => $this->version
        ]));

        //prepare the cache method if not supplied
        if ($cache === null) {
            //create a new file cache
            $cache = new CacheManager($this->config);
            $cache = $cache->setDriver('file', [
                'path'=> realpath($this->config['rootDir'].'/../tmp').'/',
            ]);
        }
        $this->cache = $cache;

        //start shieldfy guard
        $this->startGuard();
    }

    /**
      * start the actual guard
      * @return void
      */
    protected function startGuard()
    {
        $exceptionsCollector = new ExceptionsCollector($this->config);
        $requestCollector = new RequestCollector($_GET, $_POST, $_SERVER, $_COOKIE, $_FILES);
        $userCollector = new UserCollector($requestCollector);
        $codeCollector = new CodeCollector;
        $queriesCollector = new QueriesCollector;

        $this->collectors = [
            'exceptions' => $exceptionsCollector,
            'request'    => $requestCollector,
            'user'       => $userCollector,
            'code'       => $codeCollector,
            'queries'    => $queriesCollector
        ];

        $this->catchCallbacks($requestCollector, $this->config);

        //check the installation
        if (!$this->isInstalled()) {
            $install = (new Installer($requestCollector, $this->config))->run();
        }

        //check if installation failed for any reason ans skip for current session
        if (!$this->isInstalled()) {
            return;
        }

        //start new session
        $this->session = new Session($userCollector, $requestCollector, $this->config, $this->cache);


        /* monitors */
        $monitors = new MonitorsBag($this->config,
                                    $this->cache,
                                    $this->session,
                                    $this->collectors);
        $monitors->run();

        $this->exposeHeaders();
    }


    /**
     * Catch callbacks from Shieldfy API
     * @param  RequestCollector $request
     * @param  Config           $config
     * @return void
     */
    public function catchCallbacks(RequestCollector $request, Config $config)
    {
        (new CallbackHandler($request, $config))->catchCallback();
    }

    /**
     * Attach PDO Database to analyze
     * @param  PDO    $pdo
     * @return TraceablePDO
     */
    public function attachDB(PDO $pdo)
    {
        return $this->collectors['queries']->attachDB($pdo);
    }

    /**
     * Attach external query handler (used by frameworks query event handlers)
     * @param mixed $query
     * @return void
     */
    public function attachQuery($query)
    {
        $query = $this->collectors['queries'];
        $query->handler('event', $query->sql, $query->bindings);
    }

    /**
     * check if guard installed
     * @return boolean
     */
    public function isInstalled()
    {
        if (file_exists($this->config['rootDir'].'/data/installed')) {
            return true;
        }
        return false;
    }

    /**
     * Save current session , request done
     */
    public function __destruct()
    {
        //everything going good lets save this session for next run
        if ($this->session !== null) {
            $this->session->save();
        }
    }

    /**
     * Expose useful headers
     * @return void
     */
    private function exposeHeaders()
    {
        if (function_exists('header_remove')) {
            header_remove('x-powered-by');
        }

        foreach ($this->config['headers'] as $header => $value) {
            if ($value === false) {
                continue;
            }
            header($header.' : '.$value);
        }

        $signature = hash_hmac('sha256', $this->config['app_key'], $this->config['app_secret']);
        header('X-Web-Shield: ShieldfyWebShield');
        header('X-Shieldfy-Signature: '.$signature);
    }
}
