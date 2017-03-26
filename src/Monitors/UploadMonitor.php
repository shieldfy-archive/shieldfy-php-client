<?php
namespace Shieldfy\Monitors;
use Shieldfy\Jury\Judge;

class UploadMonitor extends MonitorBase
{
	use Judge;

	protected $name = "upload";

	/**
	 * run the monitor
	 */
	public function run()
	{
		//get the request info
		$request = $this->collectors['request'];
		$info = $request->getInfo('files');

		if(empty($info['files'])) return;

		//analyze uploaded files
		$this->issue('upload');

		//prepare for nested uploads
		$files = [];
		array_walk($info['files'], function($value,$key) use (&$files){
			//grap key
			$name = explode('.',$key);
			$name_key = $name[2];
			unset($name[0],$name[2]);
			$name = implode('.',$name);
			if($name_key == 'name'){
				$files[$name]['name'] = $value;
			}
			if($name_key == 'tmp_name'){
				$files[$name]['tmp_name'] = $value;
			}
		});

		$judgment = [
			'score'=>0,
			'infection' => []
		];
		foreach($files as $input => $file){
			list($score,$ruleIds) = array_values($this->analyzeFile($input,$file));
			if($score){
				$judgment['score'] += $score;
				$judgment['infection'][$input] = compact('score','ruleIds');
			}
		}

		$this->handle($judgment);
	}

	public function analyzeFile($input = '',$file = []){
		$score = 0;
		$ruleIds = [];

		//analyze name
		$nameResult = $this->sentence($file['name'],'FILES:NAME');
		if($nameResult['score']){
			$score += $nameResult['score'];
			$ruleIds = array_merge($ruleIds,$nameResult['ids']);
		}

		//analyze extention
		$extention = pathinfo( $file['name'], PATHINFO_EXTENSION);
		$extResult = $this->sentence($extention,'FILES:EXTENTION');
		if($extResult['score']){
			$score += $extResult['score'];
			$ruleIds = array_merge($ruleIds,$extResult['ids']);
		}

		//analyze content
		$content = file_get_contents($file['tmp_name']);
		//check for backdoors
		$backdoorResult = $this->sentence($content,'FILES:CONTENT','backdoor');
		if($backdoorResult['score']){
			$score += $backdoorResult['score'];
			$ruleIds = array_merge($ruleIds,$backdoorResult['ids']);
		}
		//check for xxe
		$xxeResult = $this->sentence($content,'FILES:CONTENT','xxe');
		if($xxeResult['score']){
			$score += $xxeResult['score'];
			$disableEntity = libxml_disable_entity_loader(true);
			if($disableEntity === false){
				$score += 50;
				//retrive old value : maybe developer uses it anywhere :(
				libxml_disable_entity_loader($disableEntity);
			}
			$ruleIds = array_merge($ruleIds,$xxeResult['ids']);
		}
		return compact('score','ruleIds');
	}

}
