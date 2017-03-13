<?php
namespace Shieldfy\Monitors;

class CSRFMonitor extends MonitorBase
{
	/**
	 * run the monitor
	 */
	public function run()
	{
		$request = $this->collectors['request'];
		//check origin
		if(!in_array($rquest->requestMethod,['POST','PUT','PATCH','DELETE'])) return;
		if(!isset($request->server['HTTP_ORIGIN'])) return;

		$origin = parse_url($request->server['HTTP_ORIGIN'] ,PHP_URL_HOST);
		$host = parse_url($request->server['HTTP_HOST'] ,PHP_URL_HOST);
		if(trim(strtolower($origin)) !== trim(strtolower($host))){
			//csrf attack found
			//since most of frameworks now uses csrf token & many of endpoint are ajax/api
			//and modern browsers default block violation of origin ( CORS : https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS )
			//so kind of violation of origin is not critical but worth reporting to the developer
			//so he can fix anything if needed
			$this->handle([
				'score' => 30,
				'info'  => [
					'attack' => 'csrf',
					'origin' => $origin.
					'host'   => $host,
					'payload' => $request->getInfo('post');
				]
			]);
		}
	}
}
