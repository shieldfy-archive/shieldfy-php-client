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
		$this->colelctors = $collectors;
	}

	/**
	 * Force children to have its own run function
	 */
	abstract public function run();
}