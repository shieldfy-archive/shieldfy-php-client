<?php
namespace Shieldfy\Collectors;

use Shieldfy\Collectors\RequestCollector;

class UserCollector implements Collectable
{
    /**
     * Identify the user with ID.
     */
    private $userId;

    /**
     * User IP.
     */
    private $userIp;

    /**
     * User Agent.
     */
    private $userAgent;

    /**
     * Session ID.
     */
    private $sessionId;

    /**
     * User Score.
     */
    private $score = 0;

    /**
     * Constructor.
     *
     * @param RequestCollector $request
     *
     * @return type
     */
    public function __construct(RequestCollector $request)
    {
        $this->setIp($request->server);
        $this->setId();
        $this->setUserAgent($request->server);
    }
    /**
     * Set user IP.
     *
     * @param array|array $server
     */
    public function setIp(array $server = [])
    {
        $userIp = '0.0.0.0'; //unknown ip
        if (array_key_exists('REMOTE_ADDR', $server)) {
            $userIp = $server['REMOTE_ADDR'];
        }
        if (array_key_exists('HTTP_X_FORWARDED_FOR', $server)) {
            $header = explode(',', $server['HTTP_X_FORWARDED_FOR']);
            $userIp = $header[0];
        }
        if (array_key_exists('HTTP_CLIENT_IP', $server)) {
            $userIp = $server['HTTP_CLIENT_IP'];
        }
        if (array_key_exists('HTTP_X_REAL_IP', $server)) {
            $userIp = $server['HTTP_X_REAL_IP'];
        }
        $this->userIp = $userIp;
    }

    /**
     * get user ID.
     *
     * @return ID
     */
    public function getIp()
    {
        return $this->userIp;
    }

    /**
     * Set user ID.
     */
    public function setId()
    {
        $this->userId = ip2long($this->userIp);
    }

    /**
     * get user ID.
     *
     * @return ID
     */
    public function getId()
    {
        return (int)$this->userId; //force integer
    }

    /**
     * Set User agent.
     *
     * @param array|array $server
     */
    public function setUserAgent(array $server = [])
    {
        if (array_key_exists('HTTP_USER_AGENT', $server)) {
            $this->userAgent = $server['HTTP_USER_AGENT'];
        }
    }

    /**
     * Set user session id.
     *
     * @param string $sessionID
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
    }
    /**
     * Get user session id.
     *
     * @return string $sessionID
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * Set user score.
     *
     * @param int $score
     */
    public function setScore($score)
    {
        $this->score = $score;
    }

    /**
     * Get user score.
     *
     * @return int $score
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * get user info.
     *
     * @return array info
     */
    public function getInfo()
    {
        return [
            'id'        => $this->userId,
            'ip'        => $this->userIp,
            'userAgent' => $this->userAgent,
            'sessionId' => $this->sessionId,
            'score'     => $this->score,
        ];
    }
}
