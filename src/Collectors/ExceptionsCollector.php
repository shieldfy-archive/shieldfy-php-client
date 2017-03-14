<?php
namespace Shieldfy\Collectors;

class ExceptionsCollector implements Collectable
{
    protected $original_error_handler = null;
    protected $original_exception_handler = null;

    protected $currentException = null;

    public function __construct()
    {
        // http://php.net/set_error_handler
		$this->original_error_handler = set_error_handler(array($this,'handleErrors'),E_ALL);
		// http://php.net/set_exception_handler
		$this->original_exception_handler = set_exception_handler(array($this,'handleExceptions'));
    }


    /**
	 * handle errors / warning / notice
	 * @param  [type]  $severity [description]
	 * @param  [type]  $message  [description]
	 * @param  string  $file     [description]
	 * @param  integer $line     [description]
	 * @param  [type]  $context  [description]
	 * @return [type]            [description]
	 */
	public function handleErrors($severity, $message, $file = '', $line = 0, $context = [])
	{
		//LIMITATION
		//The following error types cannot be handled with a user defined function:
		//E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING, and most of E_STRICT raised in the file where set_error_handler() is called.
		//see: http://stackoverflow.com/questions/8527894/set-error-handler-doenst-work-for-fatal-error


		$this->handleExceptions(new ErrorException($message,0,$severity,$file,$line));

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
	 * Analyzing the exceptions looking for exploits
	 * @param  Throwable $exception
	 */
	public function handleExceptions(Throwable $exception)
	{
        $this->currentException = $exception;
	}

    /**
     * Retrive exception info
     * @return Throwable $exception
     */
    public function getInfo()
    {
        return $this->currentException;
    }
}
