<?php
namespace Shieldfy\Monitors;
use Shieldfy\Config;
use Shieldfy\Cache\CacheInterface;

abstract class MonitorBase
{
	/**
	 * @var Config $config
	 * @var CacheInterface $cache
	 * @var Array $collectors
	 */
	protected $config;
	protected $cache;
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
	public function __construct(Config $config,CacheInterface $cache,array $collectors)
	{
		$this->config = $config;
		$this->cache = $cache;
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
		if($judgment['score'] >= self::HIGH ){
			//report & block
			echo 'R & B <br />';
			echo $this->name.'<br />';
			print_r($judgment);
			return;
		}

		if($judgment['score'] >= self::MEDIUM){
			//report
			echo 'R <br />';
			echo $this->name.'<br />';
			print_r($judgment);
		}

		if($judgment['score'] >= self::LOW){
			//report
			echo 'R <br />';
			echo $this->name.'<br />';
			print_r($judgment);
		}
	}
}
