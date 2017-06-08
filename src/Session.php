<?php
namespace Shieldfy;

use Shieldfy\Config;
use Shieldfy\Dispatcher\Dispatchable;
use Shieldfy\Dispatcher\Dispatcher;
use Shieldfy\Exceptions\Exceptionable;
use Shieldfy\Exceptions\Exceptioner;
use Shieldfy\Collectors\UserCollector;
use Shieldfy\Collectors\RequestCollector;
use Shieldfy\Cache\CacheInterface;

class Session implements Dispatchable, Exceptionable
{
    use Dispatcher;
    use Exceptioner;

    protected $isNew = false;
    protected $isSynced = false;
    protected $user;
    protected $request;
    protected $cache;
    protected $sessionId;
    protected $history = [];

    /**
     * Start new session
     * @param UserCollector    $user
     * @param RequestCollector $request
     * @param Config           $config
     * @param CacheInterface   $cache
     */
    public function __construct(UserCollector $user, RequestCollector $request, Config $config, CacheInterface $cache)
    {
        $this->config = $config;
        $this->user = $user;
        $this->request = $request;
        $this->cache = $cache;
        if (!$cache->has($user->getId())) {
            $this->loadNewUser();
            return;
        }
        $this->loadExistingUser();
    }

    /**
     * Session not found , lets load new user
     */
    public function loadNewUser()
    {
        $this->isNew = true;
        $response = $this->trigger('session', [
            'host'=>$this->request->getHost(),
            'user'=>$this->user->getInfo()
        ]);
        if ($response && $response->status == 'success') {
            $this->sessionId = $response->sessionId;
            $this->user->setSessionId($response->sessionId);
            $this->user->setScore($response->score);
        }
    }

    /**
     * Session found , load existing user
     */
    public function loadExistingUser()
    {
        $user = $this->cache->get($this->user->getId());
        $this->user->setSessionId($user['sessionId']);
        $this->user->setScore($user['score']);
        $this->history = $this->cache->get($this->user->getSessionId());
    }

    /**
     * Is new visit
     * @return boolean
     */
    public function isNewVisit()
    {
        return $this->isNew;
    }

    /**
     * Mark as synced to avoid duplication
     */
    public function markAsSynced()
    {
        $this->isSynced = true;
    }

    /**
     * Save the current session
     */
    public function save()
    {
        if ($this->isNew) {
            //save the user session
            $this->cache->set($this->user->getId(), $this->user->getInfo());
        }

        $this->history[time()] = $this->request->getHistoryInfo();

        if ($this->isSynced) {
            //if already synced no need to restore the info to avoid duplications
            $this->history = [];
        }

        $this->cache->set($this->user->getSessionId(), $this->history);
    }

    /**
     * Retrive session history
     */
    public function getHistory()
    {
        return $this->history;
    }
}
