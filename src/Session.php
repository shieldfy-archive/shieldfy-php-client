<?php

namespace Shieldfy;

use Shieldfy\User;
use Shieldfy\Request;
use Shieldfy\Event;
use Shieldfy\Cache\CacheInterface;

class Session
{
    /**
     * @var boolean firstvisit
     */
    protected $firstVisit = false;

    /**
     * user instance
     */
    protected $user;

    /**
     * request instance
     */
    protected $request;

    /**
     * @var string session id
     */
    protected $sessionId;

    /**
     * @var array history
     */
    protected $history = [];

    /**
     * Constructor
     * @param User $user 
     * @param Request $request 
     * @param Event $event 
     * @param CacheInterface $cache 
     */
    public function __construct(User $user, Request $request,Event $event,CacheInterface $cache)
    {
        $this->user = $user;
        $this->request = $request;
        $this->event = $event;
        $this->cache = $cache;

        if ($this->cache->has($this->user->getId())) {
            //old user;
            $this->loadExistingUser();
            return;
        }

        //new user
        $this->loadNewUser();

    }

    /**
     * loads new user 
     */
    private function loadNewUser()
    {
        $this->firstVisit = true;
        $this->analyzeUser();
    }

    /**
     * analyze the new user
     */
    private function analyzeUser()
    {
        $response = $this->event->trigger('session', ['user'=>$this->user->getInfo()]);
        if ($response && $response->status == 'success') {
            $this->sessionId = $response->sessionId;
            $this->user->setSessionId($response->sessionId);
            $this->user->setScore($response->score);
            return;
        }

        // failed for somereason , generate temporary sessionID 
        $this->user->setSessionId(md5(time() * mt_rand()));
        $this->user->setScore(0);
    }

    /**
     * load existing user
     */
    private function loadExistingUser()
    {
        $info =  $this->cache->get($this->user->getId());

        $this->user->setSessionId($info['sessionId']);
        $this->user->setScore($info['score']);

        $this->sessionId = $info['sessionId'];

        $cachedHistory = $this->cache->get($info['sessionId']);
        $this->history = (count($cachedHistory)) ? $cachedHistory : [];
    }

    /**
     * if its user first visit ?
     * @return boolean
     */
    public function isFirstVisit()
    {
        return $this->firstVisit;
    }

    /**
     * get session id
     * @return string
     */
    public function getId()
    {
        return $this->sessionId;
    }

    /**
     * get session history
     * @return array
     */
    public function getHistory()
    {
        return $this->history;
    }

    /**
     * Get session info
     * @return array
     */
    public function getInfo()
    {
        return [
            'sessionId' => $this->sessionId,
            'user' => $this->user,
            'request' => $this->request,
            'history' => $this->history
        ];
    }

    /**
     * Save the session
     * @param type|bool $saveAsHistory 
     * @return type
     */
    public function save($saveAsHistory = true)
    {
        

        $requestInfo = $this->request->getInfo();

        //we don't save everything it could be private info 
        $requestInfo['info']['params']['get'] = [];
        $requestInfo['info']['params']['post'] = [];



        if ($this->isFirstVisit()) {

            $this->cache->set($this->user->getId(), $this->user->getInfo());
            $this->cache->set($this->sessionId, [$requestInfo]);

        } else {

            if ($saveAsHistory) {

                $history = $this->history;
                $history[] = $requestInfo;
                $this->cache->set($this->sessionId, $history);

            } else {

                //clear history cache its already synced
                $this->cache->set($this->sessionId, []);
            }

        }
    }
}






/*
use Shieldfy\Analyze\Analyzer;

class Session
{
    private $data = [];
    private $firstVisit = false;

    public static function load($user)
    {
        $cache = Cache::getInstance();
        $userData = $user->getInfo();
        if (!$cache->has($userData['id'])) {
            // analyze the ip 
            return (new self())->markAsFirstVisit()->setUser($userData);
        }

        return (new self())->loadUser($userData);
    }

    private function analyzeUser($userData)
    {
        $response = Event::trigger('session', ['user'=>$userData]);
        if ($response && $response->status == 'success') {
            $this->sessionID = $response->sessionID;
            $userData['sessionID'] = $response->sessionID;
            $userData['score'] = $response->score;

            return $userData;
        }
        // failed for somereason , generate temporary sessionID 
        $userData['sessionID'] = md5(time() * mt_rand());
        $userData['score'] = 0;

        return $userData;
    }

    private function markAsFirstVisit()
    {
        $this->firstVisit = true;

        return $this;
    }

    public function isFirstVisit()
    {
        return $this->firstVisit;
    }

    public function setUser($userData)
    {
        $this->user = $this->analyzeUser($userData);

        return $this;
    }

    public function loadUser($userData)
    {
        $cache = Cache::getInstance();
        $this->user = $cache->get($userData['id']);
        $this->sessionID = $this->user['sessionID'];
        $cachedHistory = $cache->get($this->user['sessionID']);
        $this->history = (count($cachedHistory)) ? $cachedHistory : [];

        return $this;
    }

    public function analyze()
    {
        $analyzer = new Analyzer($this->data);
        $result = $analyzer->run();
        if ($result) {
            $this->result = $analyzer->getResult();
            $response = Event::trigger('activity', $this->data);
            if ($response && $response->status == 'success') {
                $incidentID = $response->incidentID;
                $this->history = [];
                $this->request = '';
                $this->save();
            } else {
                $incidentID = '';
            }

            $config = Shieldfy::getConfig();
            if ($result == 1 && $config['action'] == 'block') {
                //report to the server all the info
                Action::block($incidentID);
            }
        }
        $this->save();
    }

    private function save()
    {
        $cache = Cache::getInstance();
        // we don't save everything it could be private info 
        if ($this->request !== '') {
            $this->data['request']['info']['params']['get'] = [];
            $this->data['request']['info']['params']['post'] = [];
        }

        if ($this->isFirstVisit()) {
            $cache->set($this->user['id'], $this->user);
            $cache->set($this->sessionID, [$this->request]);
        } else {
            if ($this->request) {
                $history = $this->history;
                $history[] = $this->request;
                $cache->set($this->sessionID, $history);
            } else {
                //clear history cache its already synced
                $cache->set($this->sessionID, []);
            }
        }
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
    }
}
*/