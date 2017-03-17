<?php
namespace Shieldfy\Monitors;
use Throwable;
use use Shieldfy\Jury\Judge;
class ExceptionMonitor extends MonitorBase
{
	use Judge;
	/**
	 * run the monitor
	 * Monitor for expolits that generates errors
	 * ex: LFI , RCE [eval , serialize] , SSRF
	 * Exceptions to Monitor
	 * Warning: require(xxx):  failed to open stream: No such file or directory ==> exception message
	 * syntax error .... eval()'d code ==> exception file
	 * unserialize(): Error at offset [0-9]+ of [0-9] bytes //note: serialize fuzzing may not generate errors
	 */
	public function run()
	{
		$exceptions = $this->collectors['exceptions'];
		$exceptions->listen(function($exception){
			$this->analyze($exception);
		});
	}

	public function analyze(Throwable $exception)
	{
		$this->issue('exceptions');
		if(!$this->isInScope($exception)) return;
		//in scope lets analyze it
		$request = $this->collectors['request'];
		$info = $request->getInfo();
		$params = array_merge($info['get'],$info['post'],$info['cookies']);

		$score = 0;
		$infection = [];
		foreach($params as $param => $value){
			$result = $this->sentence($value,'REQUEST');
			if($result['score']){
				$score += $result['score'];
				$infection[] = [
					'score' 	=> $result['score'],
					'ruleIds' 	=> $result['ruleIds']
				];
			}
		}
		$this->handle([
			'score'=>$score,
			'infection'=>$infection
		]);
	}

	protected function isInScope(Throwable $exception)
	{
		$message = $exception->getMessage();
		$res = $this->sentence($message,'EXCEPTION:MSG');
		if($res['score']){
			return true;
		}

		$file = $exception->getFile();
		$res = $this->sentence($file,'EXCEPTION:FILE');
		if($res['score']){
			return true;
		}

		return false;
	}



}
