<?php
namespace Shieldfy;
use Shieldfy\Config;
use Shieldfy\Dispatcher\Dispatchable;
use Shieldfy\Dispatcher\Dispatcher;
use Shieldfy\Exceptions\Exceptionable;
use Shieldfy\Exceptions\Exceptioner;
use Shieldfy\Collectors\UserCollector;
use Shieldfy\Cache\CacheInterface;

class Session implements Dispatchable,Exceptionable
{
    use Dispatcher;
    use Exceptioner;

    protected $user;
    protected $cache;
    protected $sessionId;

    public function __construct(UserCollector $user, Config $config, CacheInterface $cache)
    {
        $this->config = $config;
        $this->user = $user;
        $this->cache = $cache;
        if(!$cache->has($user->getId())){
            $this->loadNewUser();
            return;
        }
        $this->loadExistingUser();
    }

    public function loadNewUser()
    {
        echo 'loading new user';
        $response = $this->trigger('session',['user'=>$this->user->getInfo()]);
        if($response && $response->status == 'success')
        {
            $this->sessionId = $response->sessionId;
            $this->user->setSessionId($response->sessionId);
            $this->user->setScore($response->score);
        }
    }

    public function loadExistingUser()
    {
        echo 'loading excisting user';
    }

    public function save()
    {

    }
}
