<?php
namespace Shieldfy\Extentions\CodeIgniter;

class DBProxy
{
    protected $db = null;
    protected $guard = null;

    public function __construct($db, $guard)
    {
        $this->db = $db;
        $this->guard = $guard;
    }

    public function __call($name, $parameters)
    {
        if (count($parameters) > 0) {
            $bindings = (isset($parameters[1]))? $parameters[1] : [];
            $this->guard->events->trigger('db.query', [$parameters[0],$bindings]);
        }

        return call_user_func_array([$this->db,$name], $parameters);
    }
}
