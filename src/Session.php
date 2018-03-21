<?php
namespace Shieldfy;

use Shieldfy\Config;
use Shieldfy\Exceptions\Exceptionable;
use Shieldfy\Exceptions\Exceptioner;
use Shieldfy\Collectors\UserCollector;
use Shieldfy\Collectors\RequestCollector;
use Shieldfy\Http\Dispatcher;

class Session implements Exceptionable
{
    use Exceptioner;

    protected $user;
    protected $request;
    protected $dispatcher;
    protected $sessionId;

    /**
     * Start new session handler if no session started
     * @param UserCollector    $user
     * @param RequestCollector $request
     * @param Dispatcher           $dispatcher
     */
    public function __construct(UserCollector $user,
                                RequestCollector $request,
                                Dispatcher $dispatcher)
    {
        $this->user = $user;
        $this->request = $request;
        $this->dispatcher = $dispatcher;

        //check for session handler
        if (session_status() == PHP_SESSION_NONE) {
            session_name('_swaf_request_id');
            session_start([
                'cookie_httponly' => true
            ]);
        }

        $this->getUser();
    }

    /**
     * Retrive current user info (Local|Remote)
     */
    public function getUser()
    {
        //check if there is session register to this user
        if (! $this->_isset('ShieldfyUser')) {
            $this->loadNewUser();
        } else {
            $this->loadExistingUser();
        }

        $this->_save('ShieldfyUser', [
            'sId'   => $this->sessionId,
            'ip'    => $this->user->getIp(),
            'score' => $this->user->getScore()
        ]);
    }


    /**
     * Local info found , load existing user
     */
    public function loadExistingUser()
    {
        $user = $this->_load('ShieldfyUser');
        $this->sessionId = $user['sId'];
        $this->user->setSessionId($user['sId']);
        $this->user->setScore($user['score']);
    }

    /**
     * Local info not found , Load remote info
     */
    public function loadNewUser()
    {
        $this->sessionId = $this->_generateSessionId();
        $this->user->setSessionId($this->sessionId);

        //lookup for this user & start session
        $response = $this->dispatcher->trigger('session/start', [
            'sessionId' =>  $this->sessionId,
            'host'      =>  $this->request->getHost(),
            'user'      =>  $this->user->getInfo()
        ]);

        if ($response && $response->status == 'success') {
            $this->user->setScore($response->score);
        }
    }

    public function flush()
    {

        // there is no need to block the request for sync , we will do this after request is finishing
        // close session writing to be availabe for next request
        if (function_exists('session_write_close')) {
            session_write_close();
        }
        // //finish the request and send the respond to the browser
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        // send the step to the API sever
        if ($this->dispatcher->hasData()) {

            // there is threat/warning need to be sent to the server , data is already waiting at the dispatcher
            $this->dispatcher->flush();
            return;
        }

        // Trigger Step
        $this->dispatcher->trigger('session/step', [
            'sessionId' => $this->getId(),
            'host'	=>	$this->request->getHost(),
            'info' 	=> 	array_merge(
                $this->request->getShortInfo(),
                [
                    'code' => http_response_code(),
                    'time' => time()
                ]
            ),
            'user' => $this->user->getInfo()
        ]);
    }

    public function getId()
    {
        return $this->sessionId;
    }


    /**
     * Set / Read new cache value in Session
     * @param string $key
     * @param string|null $value Optional
     * @return string|false
     */
    public function cache($key, $value = null)
    {
        if ($value) {
            return $_SESSION[$key] = $value;
        }
        return isset($_SESSION[$key])? $_SESSION[$key] : false;
    }


    private function _isset($key)
    {
        return isset($_SESSION[$key]);
    }


    private function _load($key)
    {
        return (isset($_SESSION[$key]))?json_decode($_SESSION[$key], 1):false;
    }


    private function _save($key, $data)
    {
        $_SESSION[$key] = json_encode($data);
    }

    private function _generateSessionId()
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyz';
        return substr(str_shuffle(str_repeat($pool, 10)), 0, 10).time();
    }
}
