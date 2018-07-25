<?php
namespace Shieldfy;

use Shieldfy\Monitors\SampleMonitor;

use Shieldfy\Config;
use Shieldfy\Session;
use Shieldfy\Events;
use Shieldfy\Http\Dispatcher;

class SampleAttack
{
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
    public function __construct($config, $session, $dispatcher, $collectors, $events)
    {
        $this->config = $config;
        $this->session = $session;
        $this->dispatcher = $dispatcher;
        $this->collectors = $collectors;
        $this->events = $events;
        if ( $this->collectors['request']->get['simpleattack'] == 'send' ) {
            $this->send($this->collectors['request']->get['hash']);
        }
    }

    public function send($hash)
    {
        $appHash = hash_hmac('sha256', $this->config['app_secret'], $this->config['app_key']);
        if ( $hash === $appHash ) {
            (new SampleMonitor($this->config, $this->session, $this->dispatcher, $this->collectors, $this->events))->run();
        }
    }
}
