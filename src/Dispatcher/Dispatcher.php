<?php
namespace Shieldfy\Dispatcher;

use Shieldfy\Dispatcher\ApiClient;

trait Dispatcher
{
    /**
     * @var supported events list
    */
    private $events = ['install', 'update', 'session', 'activity', 'exception'];


    /**
     * trigger the event.
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

        $api = new ApiClient($this->config);

        $res = $api->request('/'.$event, $data);
        return $res;
    }

    /*
    * hardly require throwException , all dispatchable must have exceptionable trait as well
    */
    abstract public function throwException($exception);
}
