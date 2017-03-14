<?php
namespace Shieldfy\Monitors;

class RequestMonitor extends MonitorBase
{
	/**
	 * run the monitor
	 * Monitor for request traditional attacks
	 * ex: general heavy ( xss , sqli , lfi , rfi )
	 */
	public function run()
	{
		$request = $this->collectors['request'];
	}
}
