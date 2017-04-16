<?php
namespace Shieldfy\Monitors;
use Shieldfy\Jury\Judge;
class QueryMonitor extends MonitorBase
{

	use Judge;

	protected $name = 'query';

	/**
	 * run the monitor
	 */
	public function run()
	{
		$queries = $this->collectors['queries'];
		$queries->listen(function($source, $query, $bindings){
			$this->analyze($source, $query, $bindings);
		});
	}

	public function analyze($source, $query, $bindings)
	{

		$request = $this->collectors['request'];
		$info = $request->getInfo();
		$params = array_merge($info['get'],$info['post']);
		$suspicious = [];
		foreach($params as $key => $value)
		{
			if(stripos($query, $value) !== 0)
			{
				$suspicious[$key] = $value;
			}
		}
		if(empty($suspicious)) return;
		$this->analyzeUnEscapedParameters($suspicious, $query,$bindings,$source);

	}

	protected function analyzeUnEscapedParameters($suspicious, $query , $bindings,$source)
	{
		$this->issue('query');
		$judgment = [
			'score'=>0,
			'infection'=>[]
		];

		foreach($suspicious as $key => $value)
		{
		//	$value  = $this->normalize($value);
			$result = $this->sentence($value);
			$score = 0;
			$infection = [];

			if($result['score']){
				$judgment['score'] += $result['score'];
				$judgment['infection'][$key] = $result['ruleIds'];
			}
		}
		$code = [
			'source'    => $source,
			'query'		=> $query,
			'bindings'	=> $bindings
		];
		$this->handle($judgment,$code);
	}
}
