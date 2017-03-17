<?php
namespace Shieldfy\Monitors;
use Shieldfy\Jury\Judge;

class RequestMonitor extends MonitorBase
{
	use Judge;

	protected $name = "request";

	/**
	 * run the monitor
	 * Monitor for bots traditional attacks
	 * ex: general heavy zero days RCE
	 * Increase request sensetivity for next monitors
	 */
	public function run()
	{
		$request = $this->collectors['request'];
		$info = $request->getInfo();
		$this->issue('request');

		$score = 0;
		$judgment = [];

		foreach($info['get'] as $name => $value){
			$result = $this->sentence($value,'GET');
			if($result['score']){
				$score += $result['score'];
				$judgment['infection'][$name] = [
					'score'=>$score,
					'ruleIds'=>$result['ruleIds']
				];
			}
		}

		foreach($info['post'] as $name => $value){
			$result = $this->sentence($value,'POST');
			if($result['score']){
				$score += $result['score'];
				$judgment['infection'][$name] = [
					'score'=>$score,
					'ruleIds'=>$result['ruleIds']
				];
			}
		}

		//update request sensetivity
		$request->setScore($score);

		$this->handle($judgment);
	}
}
