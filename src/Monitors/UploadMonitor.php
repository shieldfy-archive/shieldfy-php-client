<?php
namespace Shieldfy\Monitors;
use Shieldfy\Jury\Judge;

class UploadMonitor extends MonitorBase
{
	use Judge;

	/**
	 * run the monitor
	 */
	public function run()
	{

		//get the request info
		$request = $this->collectors['request'];
		$info = $request->getInfo();

		if(empty($info['files'])) return;

		//analyze uploaded files
		$this->issue('upload');
		$judgment = ['score' => 0];

		foreach($info['files'] as $key => $value) {
			$result = $this->analyzeFile($key,$value);
			if($result['score'] > 0){
				$judgment['score'] += $result['score'];
				$judgment[$key] = $result;
			}
		}
		$this->handle($judgment);
	}

	public function analyzeFile($key,$value)
	{
		//if is name
		if($this->is_name($key)){
			$extention = pathinfo( $value, PATHINFO_EXTENSION);

			$nameResult = $this->sentence( $value,'FILES:NAME');
			$extResult = $this->sentence($extention,'FILES:EXTENTION');
			return [
				'score'  => $nameResult['score'] + $extResult['score'],
				'info'   => compact('nameResult','extResult')
			];
		}

		if($this->is_content($key)){
			$content = file_get_contents($value);
			$contentResult = $this->sentence($content,'FILES:CONTENT','backdoor');
			$xmlContentResult = $this->sentence($content,'FILES:CONTENT','xxe');
			if($xmlContentResult['score'] !== 0){
				$previous = libxml_disable_entity_loader(true);
				if($previous === false){
					$xmlContentResult['score'] += 50;
					//return to default behaviour , maybe developer uses it anywhere :(
					libxml_disable_entity_loader($previous);
				}
			}

			return [
				'score'  => $contentResult['score'] + $xmlContentResult['score'],
				'info'   => compact('contentResult','xmlContentResult')
			];
		}
		return;
	}

	private function is_name($key)
	{
		if(explode('.',$key)[2] == 'name') return true;
		return false;
	}

	private function is_content($key)
	{
		if(explode('.',$key)[2] == 'tmp_name') return true;
		return false;
	}



}
