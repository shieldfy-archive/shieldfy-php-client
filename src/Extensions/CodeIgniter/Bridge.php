<?php
namespace Shieldfy\Extensions\CodeIgniter;

use Shieldfy\Guard;
use Shieldfy\Extentions\CodeIgniter\DBProxy;

class Bridge
{
    public static function load($guard, $ci)
    {
        if (property_exists($ci, 'db')) {
            $ci->db = new DBProxy($ci->db, $guard);
        }
    }

    public static function hook($guard,$hook)
    {
        $ourHook = function() use($guard)
        {
            $CI =& get_instance();
            //echo 'Called';
            // var_dump($CI);
            self::load($guard,$CI);
        };

        if(!is_null($hook) && isset($hook['post_controller']))
        {
            if (is_array($hook['post_controller']) && ! isset($hook['post_controller']['function'])){
                return array_merge($hook['post_controller'],[$ourHook]);
            }else{
                return array(
                    $hook['post_controller'],
                    $ourHook
                );
            }
        }

        return $ourHook;
    }
}
