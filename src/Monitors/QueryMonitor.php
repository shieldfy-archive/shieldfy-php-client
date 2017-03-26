<?php
namespace Shieldfy\Monitors;
use Shieldfy\Jury\Judge;
class QueryMonitor extends MonitorBase
{

	use Judge;

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

		/*
		if($source === 'statement'){
			$this->analyzeStatement($query,$params);
			return;
		}
		$this->analyzeGeneral($query,$params);
		*/
		// echo '<hr />';
		// print_r($source);
		// echo '<br />';
		// print_r($query);
		// echo '<br />';
		// print_r($params);
		// echo '<hr />';
	}

	// protected function analyzeStatement($query,$bindings)
	// {
	// 	$request = $this->collectors['request'];
	// 	$info = $request->getInfo();
	// 	$params = array_merge($info['get'],$info['post']);
	// 	$suspicious = [];
	// 	foreach($params as $key => $value)
	// 	{
	// 		foreach($bindings as $bind)
	// 		{
	// 			if($bind == $value){
	// 				$suspicious[$key] = $value;
	// 			}
	// 		}
	// 	}
	//
	// 	if(empty($suspicious)) return;
	// 	$this->analyzeUnEscapedParameters($suspicious, $query,$bindings);
	//
	// }
	// protected function analyzeGeneral($query,$args)
	// {
	// 	$request = $this->collectors['request'];
	// 	$info = $request->getInfo();
	// 	$params = array_merge($info['get'],$info['post']);
	// 	$suspicious = [];
	// 	foreach($params as $key => $value)
	// 	{
	// 		if(stripos($query, $value) !== 0)
	// 		{
	// 			$suspicious[$key] = $value;
	// 		}
	// 	}
	// 	if(empty($suspicious)) return;
	// 	$this->analyzeUnEscapedParameters($suspicious, $query,$args);
	// }

	protected function analyzeUnEscapedParameters($suspicious, $query , $bindings,$source)
	{
		$this->issue('query');
		$judgment = [
			'score'=>0,
			'infection'=>[]
		];

		foreach($suspicious as $key => $value)
		{
			$result = $this->sentence($value);
			$score = 0;
			$infection = [];

			if($result['score']){
				$judgment['score'] += $result['score'];
				$judgment['infection'][$key] = [
					'score'=>$result['score'],
					'ruleIds'=>$result['ids']
				];

			}
		}
		$judgment['info'] = [
			'source'    => $source,
			'query'		=> $query,
			'bindings'	=> $bindings
		];
		print_r($judgment);
		$this->handle($judgment);
	}
}
