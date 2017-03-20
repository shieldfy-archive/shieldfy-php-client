<?php
namespace Shieldfy\Monitors;
use Shieldfy\Config;
use Shieldfy\Cache\CacheInterface;

use Shieldfy\Dispatcher\Dispatcher;
use Shieldfy\Dispatcher\Dispatchable;

abstract class MonitorBase implements Dispatchable
{
	use Dispatcher;
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
		//file_put_contents(__dir__.'/log.txt',$this->name."\n".print_r($judgment,1));
		if($judgment['score'] >= self::HIGH ){
			//report & block
			echo 'R & B <br />';
			echo $this->name.'<br />';
			print_r($judgment);
		//	file_put_contents(__dir__.'/log.txt',$this->name."\n".print_r($judgment,1));
			return;
		}

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

			$this->trigger('activity',[
				'host' 		=> $this->collectors['request']->getHost(),
				'user' 		=> $this->collectors['user']->getId(),
				'monitor'	=> $this->name,
				'judgment'	=> $judgment,
				'info'		=> $this->collectors['request']->getProtectedInfo(),
				'history'	=> $this->session->getHistory()
			]);

			$this->session->markAsSynced();

			return;
		}
	}
}
