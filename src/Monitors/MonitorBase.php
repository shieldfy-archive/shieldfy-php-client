<?php
namespace Shieldfy\Monitors;

use Shieldfy\Config;
use Shieldfy\Session;
use Shieldfy\Cache\CacheInterface;

use Shieldfy\Dispatcher\Dispatcher;
use Shieldfy\Dispatcher\Dispatchable;

use Shieldfy\Exceptions\Exceptioner;
use Shieldfy\Response\Response;

abstract class MonitorBase implements Dispatchable
{
    use Dispatcher;
    use Exceptioner;
    use Response;
    /**
     * @var Config $config
     * @var CacheInterface $cache
     * @var Array $collectors
     */
    protected $config;
    protected $cache;
    protected $session;
    protected $collectors;
    protected $name = '';

    /**
     * Threholds
     */
    const LOW    = 20;
    const MEDIUM = 50;
    const HIGH   = 70;

    /**
     * Constructor
     * @param Config $config
     * @param CacheInterface $cache
     * @param array $collectors
     */
    public function __construct(Config $config, CacheInterface $cache, Session $session, array $collectors)
    {
        $this->config = $config;
        $this->cache = $cache;
        $this->session = $session;
        $this->collectors = $collectors;
    }

    /**
     * Force children to have its own run function
     */
    abstract public function run();

    /**
     * handle the judgment info
     * @param  array $judgment judgment informatoin
     * @return void
     */
    protected function handle($judgment, $code = '')
    {
        if (!isset($judgment['score'])) {
            return;
        } //no judgment if no score
        if ($judgment['score'] < self::LOW) {
            return;
        } //safe

        /**
         * report activity
         * incidentId , host , sessionId , monitor , judgment , info , history
         */
        $incidentId = $this->generateIncidentId($this->collectors['user']->getId());

        $res = $this->trigger('activity', [
            'incidentId'        => $incidentId,
            'host'              => $this->collectors['request']->getHost(),
            'sessionId'         => $this->collectors['user']->getSessionId(),
            'ip'                => $this->collectors['user']->getIp(), //just caution if initial session failed for any reason
            'monitor'           => $this->name,
            'judgment'          => $judgment,
            'info'              => $this->collectors['request']->getProtectedInfo(),
            'code'              => $code,
            'history'           => $this->session->getHistory()
        ]);

        //mark session as synced
        $this->session->markAsSynced();

        if ($judgment['score'] >= self::HIGH) {
            if ($this->config['action'] === 'block') {

                //ready to save the session because it will not save automatically because of halt
                $this->session->save();

                if ($this->name == 'view') {
                    return $this->respond()->returnBlock($incidentId);
                }
                $this->respond()->block($incidentId);
            }
        }
        return false;
    }

    public function forceDefaultBlock($list)
    {
        $incidentId = '###';
        foreach ($list as $header) {
            if (strpos($header, 'X-Shieldfy-Block-Id:') !== false) {
                $incidentId = trim(str_replace('X-Shieldfy-Block-Id:', '', $header));
            }
        }
        return $this->respond()->returnBlock($incidentId);
    }

    private function generateIncidentId($userId)
    {
        return $userId.time();
    }
}
