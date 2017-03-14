<?php
namespace Shieldfy\Monitors;
use Throwable;
class ExceptionMonitor extends MonitorBase
{
	protected $original_error_handler = null;
	protected $signatures = [
		'message' => [
				'/(require|require_once|include|include_once)\s*\((.*)\)\s*failed to open stream/isU',
				'/(require|require_once|include|include_once)\s*\(\)\s*:\s*failed opening required \'(.*)\'/isU',
				'/unserialize\(\):\s*Error\s*at\s*offset\s*[0-9]+\s*of\s*[0-9]+/isU'
		],
		'file' => [
			'/eval\(\)\'d code/isU'
		]
	];
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
		$x = $this->collectors['exceptions'];
		$x->listen(function($exception){
			$this->analyze($exception);
		});
	}

	public function analyze(Throwable $exception)
	{
		if(!$this->isInScope($exception)) return;
		//in scope lets analyze it
		
	}

	protected function isInScope(Throwable $exception)
	{
		$message = $exception->getMessage();
		foreach($this->signatures['message'] as $message_sign){
			if(preg_match($message_sign,$message)){
				echo 'Error In Score (message)'; return true;
			}
		}

		$file = $exception->getFile();
		foreach($this->signatures['file'] as $file_sign){
			if(preg_match($file_sign,$file)){
				echo 'Error In Score (file)'; return true;
			}
		}

		return false;
	}



}
