<?php
namespace Shieldfy\Extensions\CodeIgniter;

use Shieldfy\Guard;
use Shieldfy\Extensions\CodeIgniter\DBProxy;

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
            self::load($guard,$CI);
        };

        if(!is_null($hook) && isset($hook['post_controller_constructor']))
        {
            if (is_array($hook['post_controller_constructor']) && ! isset($hook['post_controller_constructor']['function'])){
                return array_merge($hook['post_controller_constructor'],[$ourHook]);
            }else{
                return array(
                    $hook['post_controller_constructor'],
                    $ourHook
                );
            }
        }

        return $ourHook;
    }
}
