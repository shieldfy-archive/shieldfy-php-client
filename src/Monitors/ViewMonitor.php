<?php
namespace Shieldfy\Monitors;

class ViewMonitor extends MonitorBase
{
	/**
	 * run the monitor
	 */
	public function run()
	{
		ob_start(array($this,'analyzeView'));
	}

	public function analyzeView($content)
	{
		//run rules on request
		//match with the view if found
		return $content;
	}
}
