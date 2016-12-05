<?php
namespace Shieldfy\Exceptions;
use Shieldfy\Shieldfy;
use Shieldfy\Event;
use Exception;

/**
 * ExceptionHandler is used to suppress all PHP exceptions and
 * handles them without interupting the user experience.
 */

class ExceptionHandler
{

    public static function throwException(Exception $exception){
        $config = Shieldfy::getConfig();
        if($config['debug'] === true){
            throw $exception;
        }
        return;
    }
    /**
     * Sets the error handler and logs errors
     * @return void
     */
	public static function setHandler(){
		set_error_handler(function($errno, $errstr, $errfile, $errline) {
            $path = realpath(Shieldfy::getRootDir().'/../log');
            $filename = $path.'/'.date('Ymd').'.log';
            $error = $errno.'-'.$errstr.'-'.$errfile.'-'.$errline."\n";
            file_put_contents($filename, $error ,FILE_APPEND | LOCK_EX);
    		return true;
		});

        // Catches fatal errors in the package.
        register_shutdown_function(function() {
            $lastError = error_get_last();

            if($lastError === null){
                return;
            }

            // Check if error originated from a Shieldfy class.
            if(strpos($lastError['file'], Shieldfy::getRootDir()) === false){
                return;
            }

            // Report a Shieldfy error though the Event Class.
            $response = Event::trigger('exception',[
                    'type'=>'fatal',
                    'error'=>$lastError
            ]);

        });
	}

    /**
     * Stops Shieldfy's exception handler.
     * @return void
     */
	public static function closeHandler(){
		restore_error_handler();
	}
}
