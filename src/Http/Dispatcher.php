<?php
namespace Shieldfy\Http;

use Shieldfy\Config;
use Shieldfy\Http\ApiClient;
use Shieldfy\Exceptions\Exceptioner;
use Shieldfy\Exceptions\Exceptionable;
use Shieldfy\Exceptions\EventNotExistsException;

class Dispatcher implements Exceptionable
{
    use Exceptioner;

    /**
     * @var supported events list
    */
    private $events = [
        'install',
        'update',
        'session/start',
        'session/step',
        'session/threat',
        'security/scan',
        'exception'
    ];

    private $data = [];

    public $apiClient = null;

    public $config;

    public function __construct(Config $config , ApiClient $apiClient)
    {
        $this->config = $config;
        $this->apiClient = $apiClient;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function hasData()
    {
        return count($this->data);
    }

    public function getData()
    {
        return $this->data;
    }

    public function flush()
    {
        if(count($this->data) === 0) return;
        return $this->trigger('session/threat',$this->data);
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
            return false; //return to avoid extra execution if errors is off
        }
        $data = json_encode($data);
        return $this->apiClient->request('/'.$event, $data);
    }

}
