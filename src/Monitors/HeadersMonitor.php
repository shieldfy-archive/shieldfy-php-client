<?php
namespace Shieldfy\Monitors;

class HeadersMonitor extends MonitorBase
{
	/**
	 * run the monitor
	 */
	public function run()
	{
		ob_start(array($this,'getStatus'));
	}

	public function getStatus($content)
	{
		$statusCode = http_response_code();
		//4xx || 5xx
		if($statusCode >= 400){
			$this->handle([
				'score' => 10,
				'info' 	=> [
					'statusCode' => $statusCode
				]
			]);
		}
		return $content;
	}
}
