<?php
namespace Shieldfy\Monitors;

use Shieldfy\Config;
use Shieldfy\Session;
use Shieldfy\Events;
use Shieldfy\Http\Dispatcher;

class MonitorsBag
{
    /**
     * list of available monitors
     * name => class extended from MonitorBase class
     */
    private $monitors = [
         'UserMonitor'               =>    \Shieldfy\Monitors\UserMonitor::class,
         'RequestMonitor'            =>    \Shieldfy\Monitors\RequestMonitor::class,
         'UploadMonitor'             =>    \Shieldfy\Monitors\UploadMonitor::class,
         'DBMonitor'                 =>    \Shieldfy\Monitors\DBMonitor::class,
         'MemoryMonitor'             =>    \Shieldfy\Monitors\MemoryMonitor::class,
         'ViewMonitor'               =>    \Shieldfy\Monitors\ViewMonitor::class,
         'ExceptionsMonitor'         =>    \Shieldfy\Monitors\ExceptionsMonitor::class,
         //'LibraryMonitor'            =>    \Shieldfy\Monitors\LibraryMonitor::class
    ];

    /**
     * @var Config $config
     */
    protected $config;
    protected $session;
    protected $collectors;
    protected $dispatcher;
    protected $events;

    /**
     * Constructor
     * @param Config $config
     */
    public function __construct(Config $config, Session $session, Dispatcher $dispatcher, array $collectors, Events $events)
    {
        $this->config = $config;
        $this->session = $session;
        $this->dispatcher = $dispatcher;
        $this->collectors = $collectors;
        $this->events = $events;
    }

    public function run()
    {
        foreach ($this->monitors as $monitorName => $monitorClass) {
            if (!$this->config['disable'] || !in_array($monitorName, $this->config['disable'])) {
                (new $monitorClass($this->config, $this->session, $this->dispatcher, $this->collectors, $this->events))->run();
            }
        }
    }
}
