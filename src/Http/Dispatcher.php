<?php
namespace Shieldfy\Http;

use Shieldfy\Cache;
use Shieldfy\Config;
use Shieldfy\Http\ApiClient;
use Shieldfy\Exceptions\Exceptioner;
use Shieldfy\Exceptions\Exceptionable;
use Shieldfy\Exceptions\EventNotExistsException;
use Shieldfy\Jury\ScrubbingData;

class Dispatcher implements Exceptionable
{
    use Exceptioner;

    /**
     * @var supported events list
    */
    private $events = [
        'install',
        'update',
        'update/vendors',
        'session/start',
        'session/step',
        'session/threat',
        'security/scan',
        'exception'
    ];

    private $data = null;

    public $apiClient = null;

    public $config;

    public function __construct(Config $config, ApiClient $apiClient)
    {
        $this->config = $config;
        $this->apiClient = $apiClient;
        $this->scrubbing = new ScrubbingData();
    }

    public function setData($data)
    {
        if (isset($data['request'])) {
            $data['request']['uri'] = $this->scrubbing->url($data['request']['uri']);
            $data['request']['get'] = $this->scrubbing->data($data['request']['get']);
            $data['request']['post'] = $this->scrubbing->data($data['request']['post']);
        }
        if (isset($data['charge'])) {
            $data['charge'] = $this->scrubbing->charge($data['charge']);
        }
        $this->data = $data;
    }

    public function hasData()
    {
        return ($this->data) ? true : false;
    }

    public function getData()
    {
        return $this->data;
    }

    public function flush()
    {
        if (count($this->data) === 0) {
            return;
        }
        return $this->trigger('session/threat', $this->data);
    }

    /**
     * trigger event with data
     *
     * @param string $event
     * @param array  $data
     *
     * @return result of the event | false
     */
    public function trigger($event, $data = [])
    {
        if (!in_array($event, $this->events)) {
            $this->throwException(new EventNotExistsException('Event '.$event.' not loaded', 302));
            return false; // Return to avoid extra execution if errors are off.
        }

        if (!$this->config['queue']) {
            $data = json_encode($data);
            return $this->apiClient->request('/'.$event, $data);
        }

        $cache = new Cache();
        $cache->setDriver('file', [
            'path' => __DIR__.'/../../tmp/cache/'
        ])->set(time(), [
            'event' => '/'.$event,
            'data' => $data
        ]);
        
    }
}
