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
	public function __construct(Config $config,CacheInterface $cache,Session $session, array $collectors)
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
	protected function handle($judgment)
	{
		if($judgment['score'] < self::LOW) return; //safe

		/**
		 * report activity
		 * incidentId , host , sessionId , monitor , judgment , info , history
		 */
		$incidentId = $this->generateIncidentId($this->collectors['user']->getId());
		$this->trigger('activity',[
			'incidentId' 	=> $incidentId,
			'host' 			=> $this->collectors['request']->getHost(),
			'sessionId' 	=> $this->collectors['user']->getSessionId(),
			'monitor'		=> $this->name,
			'judgment'		=> $judgment,
			'info'			=> $this->collectors['request']->getProtectedInfo(),
			'history'		=> $this->session->getHistory()
		]);

		//mark session as synced
		$this->session->markAsSynced();

		if($judgment['score'] >= self::HIGH ){
			if($this->config['action'] === 'block'){
				$this->respond()->block($incidentId);
			}
			return;
		}
		/*
		if($judgment['score'] >= self::MEDIUM){
			//report
			echo 'R <br />';
			echo $this->name.'<br />';
			print_r($judgment);
		//	file_put_contents(__dir__.'/log.txt',$this->name."\n".print_r($judgment,1));
			return;
		}

		if($judgment['score'] >= self::LOW){
			//report
			echo 'R <br />';
			echo $this->name.'<br />';
			print_r($judgment);
			//file_put_contents('./log.txt',$this->name."\n".print_r($judgment,1));
			//generate activityid
			//
			return;
		}
		*/
	}

	private function generateIncidentId($userId)
	{
		return $userId.time();
	}
}
