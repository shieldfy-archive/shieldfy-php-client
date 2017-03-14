<?php
namespace Shieldfy\Monitors;

class ApiMonitor extends MonitorBase
{
	/**
	 * run the monitor
	 */
	public function run()
	{
        $this->checkForJWT();
	}

    public function checkForJWT()
    {
        //check for headers
        $request = $this->collectors['request'];
        //echo 'hi';
        if(isset($request->server['HTTP_AUTHORIZATION']) && strpos($request->server['HTTP_AUTHORIZATION'],'Bearer') !== false){
            //echo 'x';
            //check for none algorithm (alg == 'none')
            $jwt_token = str_replace('Bearer','',$request->server['HTTP_AUTHORIZATION']);
            echo json_decode(base64_decode( explode('.',$jwt_token)[0] ),1)['alg'];
        }

    }
}
