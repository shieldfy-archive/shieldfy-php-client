<?php
namespace Shieldfy\Extentions\CodeIgniter;

class DBProxy
{
	protected $db = null;
    protected $guard = null;

    public function __construct($db,$guard)
    {
        $this->db = $db;
        $this->guard = $guard;
    }
    
    public function __call($name, $parameters)
    {

        if(count($parameters) > 0){
            $query = new stdClass;        
            $query->sql = $parameters[0];
            $query->bindings = (isset($parameters[1]))? $parameters[1] : [];
            $this->guard->attachQuery($query);
        }    
        
        return call_user_func_array([$this->db,$name], $parameters);
    }
}