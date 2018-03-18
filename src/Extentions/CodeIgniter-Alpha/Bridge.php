<?php
namespace Shieldfy\Extentions\CodeIgniter;

Use Shieldfy\Guard;
use Shieldfy\Extentions\CodeIgniter\DBProxy;

class Bridge
{
	public static function load($guard , $ci)
	{
	    if(property_exists($ci,'db')){
	        $ci->db = new DBProxy($ci->db,$guard);
	    }
	}    
}