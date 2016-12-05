<?php
namespace Shieldfy;
use Shieldfy\Shieldfy;
use Shieldfy\Event;
use Shieldfy\Exceptions\ExceptionHandler;
use Shieldfy\Exceptions\InstallationException;
class Install
{
	private static $installed = null;
    
    /**
     * check if installed or not
     * @return boolean $installed
     */
	public static function init()
	{
		if (null === self::$installed) {
			if(!self::isInstalled())
        	{
        		self::install();
        	}
            return self::$installed = true;
        }        
        return self::$installed;
	}

    /**
     * check if the script installed or not
     * @return boolean
     */
    private static function isInstalled()
    {
        if(file_exists(Shieldfy::getRootDir().'/data/installed'))
        {
            return true;
        }
        return false;
    }

    /**
     * install the package
     * @return boolean true
     */
	public static function install()
	{
		$response = Event::trigger('install',[
            'host'=>$_SERVER['HTTP_HOST'],
            'https'=>self::isSecure(),
            'ip'=>$_SERVER['SERVER_ADDR'],
            'server'=>[
                'lang'=>'php',
                'webserver'=>$_SERVER['SERVER_SOFTWARE'],
                'php_version'=>PHP_VERSION,
    			'sapi_type'=>@php_sapi_name(),
    			'os_info'=>@php_uname(),
    	        'disabled_functions'=>(@ini_get('disable_functions') ? @ini_get('disable_functions') : 'None'),
    	        'loaded_extensions'=>implode(',', @get_loaded_extensions()),
    	        'display_errors'=>@ini_get('display_errors'),
    	        'register_globals'=>(@ini_get('register_globals') ? @ini_get('register_globals') : 'None'),
    	        'post_max_size'=>@ini_get('post_max_size'),
    	        'curl'=>@extension_loaded('curl') && @is_callable('curl_init'),
    	        'fopen'=>@ini_get('allow_url_fopen'),
    			'mcrypt'=>@extension_loaded('mcrypt'),
            ]
        ]);

        if($response->status == 'success'){
            file_put_contents(Shieldfy::getRootDir().'/data/installed',time());
            $data = (array)$response->data;
            if(isset($data['site_rules'])){
                file_put_contents(Shieldfy::getRootDir().'/data/site_rules',json_encode($data['site_rules']));
            }
            if(isset($data['pre_rules'])){
                file_put_contents(Shieldfy::getRootDir().'/data/pre_rules',json_encode($data['pre_rules']));
            }
            if(isset($data['hard_rules'])){
                file_put_contents(Shieldfy::getRootDir().'/data/hard_rules',json_encode($data['hard_rules']));
            }
            if(isset($data['soft_rules'])){
                file_put_contents(Shieldfy::getRootDir().'/data/soft_rules',json_encode($data['soft_rules']));
            }
        }else{
            ExceptionHandler::throwException( new InstallationException($response->code, $response->message) );
            
        }
		return self::$installed = true;
	}

    /**
     * check if website is using ssl or not
     * @return boolean 
     */
	private static function isSecure() {
      return
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || $_SERVER['SERVER_PORT'] == 443;
    }
}