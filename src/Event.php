<?php

namespace Shieldfy;

use Shieldfy\Exceptions\EventNotExistsException;
use Shieldfy\Exceptions\ExceptionHandler;

class Event
{
    /**
     * @var $events supported events list
     */
    private $events = ['install', 'update', 'session', 'activity', 'exception'];

    /**
     * @var api
     * @var exceptionHandler
     */
    protected $api;
    protected $exceptionHandler;

    /**
     * Constructor
     * @param ApiClient $api 
     * @param ExceptionHandler $exceptionHandler 
     * @return void
     */
    public function __construct(ApiClient $api, ExceptionHandler $exceptionHandler)
    {
        $this->api = $api;
        $this->exceptionHandler = $exceptionHandler;
    }

    /**
     * trigger the event
     * @param string $event 
     * @param array $data 
     * @return result of the event | false
     */
    public function trigger($event, $data = [])
    {
        if (!in_array($event, $this->events)) {
            $this->exceptionHandler->throwException(new EventNotExistsException('Event '.$event.' not loaded'));
            return false; //return to avoid extra execution if errors is off
        }

        $data = json_encode($data);
        $res = $this->api->request('/'.$event, $data);
        return $res;
    }
}
