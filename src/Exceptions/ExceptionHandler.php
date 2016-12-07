<?php

namespace Shieldfy\Exceptions;

use Exception;
use Shieldfy\Config;

/**
 * ExceptionHandler is used to suppress all PHP exceptions and
 * handles them without interupting the user experience.
 */
class ExceptionHandler
{
    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Sets the error handler and logs errors.
     *
     * @return void
     */
    public function setHandler()
    {
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            $path = realpath($this->config['rootDir'].'/../log');
            $filename = $path.'/'.date('Ymd').'.log';
            $error = $errno.'-'.$errstr.'-'.$errfile.'-'.$errline."\n";
            file_put_contents($filename, $error, FILE_APPEND | LOCK_EX);

            return true;
        });

        // Catches fatal errors in the package.
        register_shutdown_function(function () {
            $lastError = error_get_last();

            if ($lastError === null) {
                return;
            }

            // Check if error originated from a Shieldfy class.
            if (strpos($lastError['file'], $this->config['rootDir']) === false) {
                return;
            }

            $path = realpath($this->config['rootDir'].'/../log');
            $filename = $path.'/'.date('Ymd').'.log';
            $error = json_encode($lastError);
            $error .= "\n";
            file_put_contents($filename, $error, FILE_APPEND | LOCK_EX);
        });
    }

    public function throwException(Exception $exception)
    {
        if ($this->config['debug'] === true) {
            throw $exception;
        }
    }

    /**
     * Stops Shieldfy's exception handler.
     *
     * @return void
     */
    public function closeHandler()
    {
        restore_error_handler();
    }
}
