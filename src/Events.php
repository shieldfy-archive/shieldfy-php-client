<?php
namespace Shieldfy;

class Events
{
    protected $pipe = [];

    public function listen($eventName, $callBack)
    {
        $this->pipe[$eventName] = $callBack;
    }

    public function trigger($eventName, $data = [])
    {
        //dd($this->pipe[$eventName]);
        if (!isset($this->pipe[$eventName])) {
            return false;
        }
        return call_user_func_array($this->pipe[$eventName], $data);
    }
}
