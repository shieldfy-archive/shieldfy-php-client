<?php
namespace Shieldfy;
use Shieldfy\Shieldfy;
use Shieldfy\Cache;
use Shieldfy\User;
use Shieldfy\Request;
use Shieldfy\Session;
use Shieldfy\Headers;
use Shieldfy\Callbacks\CallbackHandler;
use Shieldfy\Exceptions\ExceptionHandler;

class Guard
{
    /**
     * Initialize Shielfy guard.
     * @param Array $config
     * @return object
     */

    public static function init(Array $config)
    {
        // Defines Shieldfy's own exception handler
        ExceptionHandler::setHandler();

        $config = Shieldfy::setConfig($config);

        // If no cache instance found, init FileCacheDriver
        // inside the package folder

        if(Cache::getInstance() === null){
            Cache::setDriver('file', [
                'path'=>realpath(__dir__.'/../tmp').'/'
            ]);
        }

        
        //Send required headers
        Headers::expose($config['disabledHeaders']);

        // Check if Shieldfy is installed.
        Install::init();

        //capture the current user
        $user = new User();

        //capture the current request
        $request = new Request();

        //load session details
        $session = Session::load($user);

        $session->request = [
                'created'=>time(),
                'info' => $request->getInfo()
        ];

        //start analyzing
        $session->analyze();

        // Close Shieldfy's exception handler
        ExceptionHandler::closeHandler();

        return new CallbackHandler();
    }

    /**
     * A mirror to catchCallbacks in callback handler
     * @return void
     */

    public static function catchCallbacks(){
        $handler = new CallbackHandler();
        $handler->catchCallbacks();
    }
}
