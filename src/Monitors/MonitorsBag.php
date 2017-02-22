<?php
namespace Shieldfy\Monitors;

use Shieldfy\Config;
use Shieldfy\Cache\CacheInterface;

class MonitorsBag
{
	/**
	 * list of available monitors
	 * name => class extended from MonitorBase class
	 */
	private $monitors = [
		'UserMonitor'		=>	\Shieldfy\Monitors\UserMonitor::class,
		'UploadMonitor'		=>	\Shieldfy\Monitors\UploadMonitor::class,
		'CSRFMonitor'		=>	\Shieldfy\Monitors\CSRFMonitor::class,
		'RequestMonitor'	=>	\Shieldfy\Monitors\RequestMonitor::class,
		'ExceptionMonitor'	=>	\Shieldfy\Monitors\ExceptionMonitor::class,		
		'QueryMonitor'		=>	\Shieldfy\Monitors\QueryMonitor::class,
		'ViewMonitor'		=>	\Shieldfy\Monitors\ViewMonitor::class,
		'HeadersMonitor'	=>	\Shieldfy\Monitors\HeadersMonitor::class
	];

	/**
	 * @var Config $config
	 * @var CacheInterface $cache
	 * @var Array $collectors
	 */
	protected $config;
	protected $cache;
	protected $collectors;

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

	public function run()
	{
		foreach($this->monitors as $monitorName => $monitorClass)
		{
			if(!in_array($monitorName, $this->config['disable']))
			{
				(new $monitorClass($this->config,$this->cache,$this->collectors))->run();
			}
		}
	}
}