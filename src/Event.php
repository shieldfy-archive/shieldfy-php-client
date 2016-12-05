<?php
namespace Shieldfy;
use Shieldfy\User;
use Shieldfy\ApiClient;
use Shieldfy\Exceptions\ExceptionHandler;
use Shieldfy\Exceptions\EventNotExists;

class Event
{
    private static $events = ['install','update','session','activity','exception'];
    public static $apiClient = null;

    public static function trigger($event,$data = []){

        if(!in_array($event,self::$events)){
            ExceptionHandler::throwException(new EventNotExists('Event '.$event.' not loaded'));
            return; //return to avoid extra execution if errors is off
        }

        //check if apiclient set
        if(self::$apiClient instanceof ApiClient === false){
            self::$apiClient = new ApiClient;
        }

        $data = json_encode($data);
        $res = self::$apiClient->request('/'.$event,$data);
        return $res;
    }
}
