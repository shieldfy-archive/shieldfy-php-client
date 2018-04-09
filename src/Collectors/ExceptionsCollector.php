<?php
namespace Shieldfy\Collectors;

use ErrorException;
use Closure;
use Shieldfy\Config;
use Shieldfy\Http\Dispatcher;

/**
 * Exceptions Collector
 */
class ExceptionsCollector implements Collectable
{
    protected $config;
    protected $dispatcher;
    protected $original_error_handler = null;
    protected $original_exception_handler = null;
    protected $callback = null;

    /**
     * Constructor
     */
    public function __construct(Config $config, Dispatcher $dispatcher)
    {
        $this->config = $config;
        $this->dispatcher = $dispatcher;
        // http://php.net/set_error_handler
        $this->original_error_handler = set_error_handler(array($this,'handleErrors'), E_ERROR | E_WARNING | E_PARSE);
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
     * @param  array  $context (Deprecated in php 7.2)
     */
    public function handleErrors($severity = 0, $message = '', $file = '', $line = 0)
    {
        //LIMITATION
        //The following error types cannot be handled with a user defined function:
        //E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING, and most of E_STRICT raised in the file where set_error_handler() is called.
        //see: http://stackoverflow.com/questions/8527894/set-error-handler-doenst-work-for-fatal-error

        $this->handleExceptions(new ErrorException($message, 0, $severity, $file, $line), false);

        if ($this->original_error_handler !== null) {
            call_user_func($this->original_error_handler,
                            $severity,
                            $message,
                            $file,
                            $line);
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

        $this->handleExceptions(new ErrorException($message, 0, $severity, $file, $line), false);
    }

    /**
     * Analyzing the exceptions looking for exploits
     * @param  Throwable $exception
     * @param  Boolean $is_exception
     */
    public function handleExceptions($exception, $is_exception = true)
    {
        if ($this->callback !== null) {
            call_user_func($this->callback, $exception);
        }

        if (strpos($exception->getFile(), $this->config['rootDir']) !== false) {
            $this->logInternalError($exception);
            //if debug and no external error handler show the error
            if (
                $is_exception &&
                $this->original_exception_handler === null &&
                $exception->getCode() > 0 &&
                $this->config['debug'] === true
                ) {
                throw $exception;
            }
        }

        if ($is_exception && $this->original_exception_handler !== null) {
            return call_user_func($this->original_exception_handler, $exception);
        }

        if ($is_exception) {
            throw $exception;
        }
    }

    /**
     * Log internal errors regarded shieldfy
     * @param  Throwable $exception
     * @return void
     */
    protected function logInternalError($exception)
    {
        if (!is_writable($this->config['logsDir'])) {
            return;
        }

        $logFile = $this->config['logsDir'].DIRECTORY_SEPARATOR.date('Ymd').'.log';

        // No need to delay the request any more lets finish it
        // close session writing to be availabe for next request
        if (function_exists('session_write_close')) {
            session_write_close();
        }
        //finish the request and send the respond to the browser
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        // tel the API
        $response = $this->dispatcher->trigger('exception', [
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'message' => $exception->getMessage(),
            'old'     => (file_exists($logFile)) ? file_get_contents($logFile) : ''
        ]);

        if ($response && $response->status == 'success') {
            //unlink the old file
            if (file_exists($logFile)) {
                unlink($logFile);
            }
            return;
        }

        //contacting server failed for somereason , store locally
        $error = time().'-'.$exception->getCode().'-'.$exception->getFile().'-'.$exception->getLine().'-'.$exception->getMessage()."\n";
        file_put_contents($logFile, $error, FILE_APPEND | LOCK_EX);
        return;
    }

    /**
     * @return array []
     */
    public function getInfo()
    {
        return [ ];
    }
}
