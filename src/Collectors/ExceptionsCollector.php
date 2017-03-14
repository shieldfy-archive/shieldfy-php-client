<?php
namespace Shieldfy\Collectors;
use ErrorException;
use Throwable;
use Closure;
use Shieldfy\Config;

/**
 * Exceptions Collector
 */
class ExceptionsCollector implements Collectable
{
    protected $config;
    protected $original_error_handler = null;
    protected $original_exception_handler = null;
    protected $callback = null;

    /**
     * Constructor
     */
    public function __construct(Config $config)
    {
        $this->config = $config;

        // http://php.net/set_error_handler
		$this->original_error_handler = set_error_handler(array($this,'handleErrors'),E_ALL);
		// http://php.net/set_exception_handler
		$this->original_exception_handler = set_exception_handler(array($this,'handleExceptions'));

        // http://php.net/register_shutdown_function
        register_shutdown_function(array($this,'handleFatalErrors'));
    }

    /**
     * add listener to errors
     * @param  Closure $callback
     */
    public function listen(Closure $callback)
    {
        $this->callback = $callback;
    }


    /**
	 * Handle errors / warning / notice
	 * @param  Integer  $severity
	 * @param  String  $message
	 * @param  string  $file
	 * @param  integer $line
	 * @param  array  $context
	 */
	public function handleErrors($severity = 0, $message = '', $file = '', $line = 0, $context = [])
	{
		//LIMITATION
		//The following error types cannot be handled with a user defined function:
		//E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING, and most of E_STRICT raised in the file where set_error_handler() is called.
		//see: http://stackoverflow.com/questions/8527894/set-error-handler-doenst-work-for-fatal-error

		$this->handleExceptions(new ErrorException($message,0,$severity,$file,$line),false);

		if($this->original_error_handler !== null){
			call_user_func( $this->original_error_handler,
							$severity,
                    		$message,
                			$file,
                    		$line,
                    		$context );
		}
	}

    /**
     * Handle fatal errors
     */
    public function handleFatalErrors()
    {
        if (null === ($error = error_get_last())) {
            return;
        }

        $message = (isset($error['message']))?$error['message']:'';
        $severity =  (isset($error['type']))?$error['type']:0;
        $file =  (isset($error['file']))?$error['file']:'';
        $line =  (isset($error['line']))?$error['line']:0;

        $this->handleExceptions(new ErrorException($message,0,$severity,$file,$line),false);
    }

	/**
	 * Analyzing the exceptions looking for exploits
	 * @param  Throwable $exception
	 * @param  Boolean $is_exception
	 */
	public function handleExceptions(Throwable $exception,$is_exception = true)
	{

        if($this->callback !== null)  call_user_func($this->callback , $exception);

        if(strpos($exception->getFile(), $this->config['rootDir']) !== false){
            $this->logInternalError($exception);
        }

        if($is_exception && $this->original_exception_handler !== null){
            call_user_func($this->original_exception_handler , $exception);
        }
	}

    /**
     * Log internal errors regarded shieldfy
     * @param  Throwable $exception [description]
     * @return [type]               [description]
     */
    protected function logInternalError(Throwable $exception)
    {
        $path = realpath($this->config['rootDir'].'/../log');
        $filename = $path.'/'.date('Ymd').'.log';
        $error = $exception->getCode().'-'.$exception->getMessage().'-'.$exception->getFile().'-'.$exception->getLine()."\n";
        file_put_contents($filename, $error, FILE_APPEND | LOCK_EX);
        return true;
    }

    /**
     * @return array []
     */
    public function getInfo()
    {
        return [ ];
    }
}
