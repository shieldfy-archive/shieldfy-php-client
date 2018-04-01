<?php
namespace Shieldfy\Extensions;

class DBProxy
{
    protected static $db = null;
    protected $guard = null;

    public function __construct($db, $guard)
    {
        self::$db = $db;
        $this->guard = $guard;
    }

    public function __get($name)
    {
        return self::$db->$name;
    }

    public function __set($name, $value)
    {
        return self::$db->$name = $value;
    }

    public function __isset($name)
    {
        return isset(self::$db->$name);
    }

    public function __unset($name)
    {
        unset(self::$db->$name);
    }

    public static function __classStatic($name, $parameters)
    {
        return call_user_func_array([self::$db,$name], $parameters);
    }
    public function __call($name, $parameters)
    {
        if (count($parameters) > 0) {
            $bindings = (isset($parameters[1]))? $parameters[1] : [];
            $this->guard->events->trigger('db.query', [$parameters[0],$bindings]);
        }

        return call_user_func_array([self::$db,$name], $parameters);
    }
}
