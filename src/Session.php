<?php

namespace Shieldfy;

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
            /* analyze the ip */
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
        /* failed for somereason , generate temporary sessionID */
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
        /* we don't save everything it could be private info */
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
