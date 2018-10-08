<?php
namespace Shieldfy\Monitors;

use Closure;
use Shieldfy\Config;
use Shieldfy\Session;
use Shieldfy\Events;
use Shieldfy\Http\Dispatcher;
use Shieldfy\Response\Response;

abstract class MonitorBase
{
    use Response;
    /**
     * @var Config $config
     */
    protected $config;
    protected $collectors;
    protected $session;
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

    /**
     * Force children to have its own run function
     */
    abstract public function run();

    /**
     * handle the judgment info
     * @param  array $judgment judgment informatoin
     * @return void
     */

    protected function sendToJail($severity = 'low', $charge  = [], $code = [])
    {

        // Based on severity and config. Let's judge it.
        $incidentId = $this->generateIncidentId($this->collectors['user']->getId());

        if ($this->dispatcher->hasData() && $severity  != 'high') {
            // Merge.
            $data = $this->dispatcher->getData();
            if ($data['charge']['key'] == $charge['key']) {
                // Same.
                $charge['score'] += $data['charge']['score'];
                $charge['rulesIds'] = array_merge($data['charge']['rulesIds'], $charge['rulesIds']);
                // Recalculate the severity.
                $severity = $this->parseScore($charge['score']);
            }
        }

        $this->dispatcher->setData([
            'incidentId'        => $incidentId,
            'host'              => $this->collectors['request']->getHost(),
            'sessionId'         => $this->session->getId(),
            'user'              => $this->collectors['user']->getInfo(),
            'monitor'           => $this->name,
            'severity'          => $severity,
            'charge'            => $charge,
            'request'           => $this->collectors['request']->getProtectedInfo(),
            'code'              => $code,
            'response'          => ($severity == 'high' && $this->config['action'] == 'block') ? 'block' : 'pass'
        ]);

        if ($severity == 'high' && $this->config['action'] == 'block') {
            if ($this->name == 'view') {
                return $this->respond()->returnBlock($incidentId);
            }
            $this->respond()->block($incidentId);
        }
        return;
    }

    protected function parseScore($score = 0)
    {
        if ($score >= 70) {
            return 'high';
        }
        if ($score >= 40) {
            return 'med';
        }
        return 'low';
    }


    private function generateIncidentId($userId)
    {
        return md5($userId.uniqid().mt_rand());
    }
}
